<?php declare(strict_types=1);

namespace AlgorandPayments\Service;

use Shopware\Core\Checkout\Order\Aggregate\OrderTransaction\OrderTransactionStateHandler;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Checkout\Payment\Cart\AsyncPaymentTransactionStruct;
use Shopware\Core\Checkout\Payment\Cart\PaymentHandler\AsynchronousPaymentHandlerInterface;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Validation\DataBag\RequestDataBag;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class AlgorandPayment implements AsynchronousPaymentHandlerInterface
{
    private RouterInterface $router;
    private OrderTransactionStateHandler $transactionStateHandler;
    /**
     * @var EntityRepositoryInterface
     */
    private EntityRepositoryInterface $orderRepository;
    /**
     * @var AlgorandPriceApi
     */
    private AlgorandPriceApi $priceApi;

    public function __construct(
        RouterInterface $router,
        OrderTransactionStateHandler $transactionStateHandler,
        EntityRepositoryInterface $orderRepository,
        AlgorandPriceApi $priceApi
    ) {
        $this->router = $router;
        $this->transactionStateHandler = $transactionStateHandler;
        $this->orderRepository = $orderRepository;
        $this->priceApi = $priceApi;
    }

    public function pay(
        AsyncPaymentTransactionStruct $transaction,
        RequestDataBag $dataBag,
        SalesChannelContext $salesChannelContext
    ): RedirectResponse {
        $this->orderRepository->update([[
            'id' => $transaction->getOrder()->getId(),
            'customFields' => [
                'algorand_payment_price' => $this->getAlgoPrice($transaction->getOrder())
            ]
        ]], $salesChannelContext->getContext());

        $redirectUrl = $this->router->generate('frontend.checkout.algorand.payment', [
            'orderId' => $transaction->getOrder()->getId(),
            'returnUrl' => $transaction->getReturnUrl()
        ]);
        return new RedirectResponse($redirectUrl);
    }

    public function finalize(
        AsyncPaymentTransactionStruct $transaction,
        Request $request,
        SalesChannelContext $salesChannelContext
    ): void {
        $paymentState = $request->query->getAlpha('status');
        $context = $salesChannelContext->getContext();
        if ($paymentState === 'completed') {
            // Payment completed, set transaction status to "paid"
            $this->transactionStateHandler->paid($transaction->getOrderTransaction()->getId(), $context);
        } else {
            // Payment not completed, set transaction status to "open"
            $this->transactionStateHandler->reopen($transaction->getOrderTransaction()->getId(), $context);
        }
    }

    private function getAlgoPrice(OrderEntity $order): float
    {
        return $order->getAmountTotal() / $this->priceApi->getPrice('USD');
    }
}
