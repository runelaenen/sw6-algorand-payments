<?php declare(strict_types=1);

namespace AlgorandPayments\Controller;

use AlgorandPayments\Service\AlgoExplorerApi;
use Shopware\Core\Checkout\Order\OrderEntity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Storefront\Controller\StorefrontController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @RouteScope(scopes={"storefront"})
 */
class PaymentController extends StorefrontController
{
    private EntityRepositoryInterface $orderRepository;
    private AlgoExplorerApi $algoExplorerApi;

    public function __construct(
        EntityRepositoryInterface $orderRepository,
        AlgoExplorerApi $algoExplorerApi
    ) {
        $this->orderRepository = $orderRepository;
        $this->algoExplorerApi = $algoExplorerApi;
    }

    /**
     * @Route("/algorand/payment/{orderId}", name="frontend.checkout.algorand.payment", options={"seo"="false"}, methods={"GET", "POST"})
     */
    public function payment(SalesChannelContext $context, string $orderId, Request $request)
    {
        $order = $this->orderRepository->search(new Criteria([$orderId]), $context->getContext())->first();
        if (!$order) {
            dd($orderId);
        }

        $showNoTransactionFoundError = false;
        if ($request->request->has('check')) {
            if($algoTransaction = $this->findTransaction($order)) {
                return new RedirectResponse(
                    $request->get('returnUrl') . '&status=completed&algo_id=' . $algoTransaction['id']
                );
            }

            $showNoTransactionFoundError = true;
        }

        return $this->renderStorefront('@Storefront/storefront/algorand/payment-screen.html.twig', [
            'returnUrl' => $request->get('returnUrl'),
            'orderId' => $orderId,
            'orderNumber' => $order->getOrderNumber(),
            'algorandPrice' => $order->getCustomFields()['algorand_payment_price'],
            'showNoTransactionFoundError' => $showNoTransactionFoundError
        ]);
    }

    private function findTransaction(OrderEntity $order): ?array
    {
        $orderNote = base64_encode($order->getOrderNumber());
        $transactions = $this->algoExplorerApi->getTransactionsWithNotePrefixWithAlgos(
            $order->getOrderNumber(),
            (float) $order->getCustomFields()['algorand_payment_price']
        );

        foreach($transactions as $transaction) {
            if (!array_key_exists('note', $transaction)) {
                continue;
            }
            if ($transaction['note'] === $orderNote) {
                return $transaction;
            }
        }
        return null;
    }
}
