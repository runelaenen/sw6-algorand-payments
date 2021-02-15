# Algorand Payment method for Shopware 6

This Shopware 6 plugin is a **proof of concept**, and should probably not be used as-is on a live environment.

Check out the POC in this 1:23 minute Youtube video: https://youtu.be/vx6k7O2ZwEQ

![Algorand payments](https://user-images.githubusercontent.com/3930922/107990907-c80dd900-6fd5-11eb-87b0-7379fb74205a.png)

Shopware is a European e-commerce platform which powers over 100.000 webshops across the world. Since the launch of
it's newest version, Shopware 6, the popularity has boomed. Currently, only *traditional* payment methods are available.
With this Algorand plugin Proof Of Concept, I want to open the eyes of Shopware developers everywhere, and welcome them
into the world of cryptocurrency. Using Algorand, we're able to receive payments fast, secure, and with small transaction 
fees.

### Why another payment plugin?
At the moment, there is no other PHP implementation of any Algorand payment solution written in PHP. Only solutions 
written in other languages, or of bad quality are available. With this POC, I try to show that a quality integration
in a webshop in PHP is also possible. I hope this POC can be an example for many developers, writing Algorand 
applications, Shopware plugins, or both.

### How does it work?
While Algorand (and most cryptocurrencies) are decentralized in nature, this POC makes use of the AlgoExplorer APIs to
search and validate transactions, and thus makes use of a centralized component. The API can however be switched out
easily for another (for example self hosted) node.

For the fiat to crypto-conversion, the API offers an integration with the APIs of Binance. At the moment of writing,
only USD (ALGO/USDT) is implemented, but it should be straightforward to calculate other currencies, or even implement
another API altogether.

### Testing
For testing purpouses, the plugin can be configured to use the TestNet network. Use the official Algorand wallet app
to give yourself some Algorand, so you can test out the integration.

### Setup and configuration
#### Shopware install
Clone the plugin to it's folder under `/custom/plugins/AlgorandPayments` in your Shopware installation. Run the following
commands in the CLI, or follow the needed steps in the admin.
```bash
bin/console plugin:refresh
bin/console plugin:install --activate AlgorandPayments
bin/console cache:clear
```

#### Configuration
It's very simple to get this plugin up and running, as there are only 3 configuration fields.

1. Node settings: You can choose wether to use the MainNet or TestNet.
2. Rate Conversion API: You can choose between different api's for the rate conversion. Currently only Binance is 
available out of the box
3. Algorand wallet address: The address where the funds should be transferred to.

### What's the flow?
After a customer chooses to pay with Algorand, the `AlgoranPayment` starts working on generating the needed information
to receive the payment using the Algorand network. First of all, we use the conversion api's to calculate how many 
ALGOs we have to ask from the customer. Then we show the customer the needed details: The Algorand address from the 
configuration, the amount he needs to pay, and a note (the order number) he has to provide with the payment.

![Screenshot frontend](https://user-images.githubusercontent.com/3930922/107992158-6307b280-6fd8-11eb-864d-78aeba241d1b.png)
!!! tip
    We also generate a QR code so the customer can scan it directly from his official Algorand app!

After the customer paid, he can click the 'Proceed' button. Because this is a POC, this is not an automated process,
in a full blown app, you'd make it so that it checks it every x seconds in the background, so the customer can be 
redirected automatically. But for the sake of this POC, I left that out of scope.
As soon as the customer clicks, we reload the page and ask the AlgoExplorer API to list all transactions that are for 
the correct wallet address, have the correct note, and have actually paid enough. As soon as such a transaction exists,
we mark the order as paid, and redirect the customer to the success page.

### Improvements
As repeated some times in this readme, this plugin is a POC. It could use some improvements before it's actually
usable. This is a small 'roadmap' for anyone who would like to build on this:
 - Implement more currencies, currently only USD is supported
 - Implement a better security between the payment page, and the Shopware return url. Currenctly the payment status is passed through a GET parameter.
 - Improve the payment screen in the frontend
 - Add live reloading in the payment screen by polling the api. (I would rewrite this to call the API asynchronously, so the payment also gets processed in the background if the customer closes his browser)
 - Rewrite usage of AlgoExplorer api to still only call it at the most once every few seconds, even if a hundred orders happen at once. (This can be done by loading the transactions, caching, and filtering them server-side instead of using the filters of the API)
 - ...

Any PRs are welcome ;-)
