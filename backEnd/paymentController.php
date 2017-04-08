<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use PayPal\Api\Amount;
use PayPal\Api\Details;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\ExecutePayment;
use PayPal\Api\PaymentExecution;

class paymentController extends Controller
{

    //api paypal credentials
    public $apiContext = "";

    //construct the php credential variable
    public function __construct(){
        $this->apiContext = new \PayPal\Rest\ApiContext(
          new \PayPal\Auth\OAuthTokenCredential(
            env('PAYPAL_CLIENTID'), //clientId
            env('PAYPAL_CLIENTSECRET')  //clientSecret
          )
        );
    }

    public function createPayment(){
        // Create new payer and method
        $payer = new Payer();
        $payer->setPaymentMethod("paypal");

        // Set redirect urls
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl('http://localhost:3000/process.php')
          ->setCancelUrl('http://localhost:3000/cancel.php');

        // Set payment amount
        $item1 = new Item();
        $item1->setName('Ground Coffee 40 oz')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("123123") // Similar to `item_number` in Classic API
            ->setPrice(8);
        $item2 = new Item();
        $item2->setName('Granola bars')
            ->setCurrency('USD')
            ->setQuantity(1)
            ->setSku("321321") // Similar to `item_number` in Classic API
            ->setPrice(2);
        $itemList = new ItemList();
        $itemList->setItems(array($item1, $item2));

        $amount = new Amount();
        $amount->setCurrency("USD")
          ->setTotal(10);
        //   ->setDetails($details);

          $transaction = new Transaction();
          $transaction->setAmount($amount)
              ->setItemList($itemList)
              ->setDescription("Payment description")
              ->setInvoiceNumber(100);

        // Create the full payment object
        $payment = new Payment();
        $payment->setIntent('sale')
          ->setPayer($payer)
          ->setRedirectUrls($redirectUrls)
          ->setTransactions(array($transaction));

        try {
            $payment->create($this->apiContext);
        } catch (Exception $ex) {
            // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
            ResultPrinter::printError("Created Payment Using PayPal. Please visit the URL to Approve.", "Payment", null, $request, $ex);
            exit(1);
        }
        // ### Get redirect url
        // The API response provides the url that you must redirect
        // the buyer to. Retrieve the url from the $payment->getApprovalLink()
        // method
        $approvalUrl = $payment->getApprovalLink();
        // NOTE: PLEASE DO NOT USE RESULTPRINTER CLASS IN YOUR ORIGINAL CODE. FOR SAMPLE ONLY
        return $payment;
        $temp = json_decode($payment);
        $returnVal = json_encode( array("paymentID" => $temp->id));
        // return $payment;
        return $returnVal;
    }

    public function executePayment(){

        // Get payment object by passing paymentId
        $paymentId = $_POST['payToken'];
        $payment = Payment::get($paymentId, $this->apiContext);
        var_dump($payment);
        return;
        $payerId = $_POST['payerId'];

        // Execute payment with payer id
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        try {
          // Execute payment
          $result = $payment->execute($execution, $this->apiContext);
          var_dump($result);
        } catch (PayPal\Exception\PayPalConnectionException $ex) {
          echo $ex->getCode();
          echo $ex->getData();
          die($ex);
        } catch (Exception $ex) {
          die($ex);
        }
    }
}
