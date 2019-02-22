<?php

namespace angelleye\PayPal\rest\checkout_orders;

/**
 *	An open source PHP library written to easily work with PayPal's API's
 *	
 *	Email:  service@angelleye.com
 *  Facebook: angelleyeconsulting
 *  Twitter: angelleye
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 * @package			paypal-php-library
 * @author			Andrew Angell <service@angelleye.com>
 * @link			https://github.com/angelleye/paypal-php-library/
 * @website			http://www.angelleye.com
 * @support         http://www.angelleye.com/product/premium-support/
 * @version			v2.0.4
 * @filesource
*/

use \angelleye\PayPal\CheckoutOrdersClass;
use \angelleye\PayPal\RestClass;

/**
 * CheckoutOrdersAPI.
 * This class is responsible for Checkout Orders V2 API & bridge class between the REST API class and Angelleye PayPal Library.
 *
 * @package 		paypal-php-library
 * @author			Andrew Angell <service@angelleye.com>
 */
class CheckoutOrdersAPI extends RestClass {

    /**
     * Private vairable to fetch and return @PayPal\Rest\ApiContext object.
     *
     * @var \PayPal\Rest\ApiContext $_api_context 
     */
    private $_api_context;

    /**
	 * Constructor
	 *
	 * @access	public
	 * @param	mixed[]	$configArray Array structure providing config data
	 * @return	void
	 */
    public function __construct($configArray) {
        parent::__construct($configArray);
        $this->_api_context = $this->get_api_context();
    }

    /**
     * Lists disputes with a full or summary set of details. Default is a summary set of details, which shows the dispute_id, reason, status, dispute_amount, create_time, and update_time fields.
     *
     * @param Array $parameters
     * @return Array|Object
     */
    public function CreateOrder($parameters) {

        $requestBody = array(
            'intent' => isset($parameters['intent']) ? $parameters['intent'] : '',
            'application_context' => isset($parameters['application_context']) ? $parameters['application_context'] : '',
            'purchase_units' => array(
              isset($parameters['purchase_units']) ? $parameters['purchase_units'] : '',
            ),
            'payer' => (isset($parameters['payer']) && !empty($parameters['payer'])) ? $parameters['payer'] : ''
        );
        try {

            $orderObject = new CheckoutOrdersClass();
            $params = array_filter($requestBody);
            $requestArray = json_encode($params);
            $order = $orderObject->create_order($params,$this->_api_context);
            $approval_link = $order->getApprovalLink();
            $returnArray['RESULT'] = 'Success';
            $returnArray['APPROVAL_LINK'] = $approval_link;
            $returnArray['ORDER'] = $order->toArray();
            $returnArray['RAWREQUEST']=$requestArray;
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;            
        } catch (\PayPal\Exception\PayPalConnectionException $ex) {
           return $this->createErrorResponse($ex);
        }
    }


    public function CaptureOrder($order_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->capture($order_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['TRANSACTION_ID'] = $order->getCaptureId();
            $returnArray['ORDER']=$order->toArray();
            $returnArray['RAWREQUEST']='{order_id:'.$order_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function AuthorizeOrder($order_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->authorize($order_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            //$returnArray['TRANSACTION_ID'] = $order->getCaptureId();
            $returnArray['ORDER']=$order->toArray();
            $returnArray['RAWREQUEST']='{order_id:'.$order_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function GetOrderDetails($order_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->get($order_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            if(isset($order->intent) && $order->intent=='CAPTURE'){
                $returnArray['TRANSACTION_ID'] = $order->getCaptureId();
            }
            if(isset($order->intent) && $order->intent=='AUTHORIZE'){
                $authData = $order->getAuthId();
                $returnArray['TRANSACTION_ID'] = $authData['id'];
                $returnArray['AUTH_STATUS'] = $authData['status'];
            }
            $returnArray['ORDER']=$order->toArray();
            $returnArray['RAWREQUEST']='{order_id:'.$order_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function GetAuthorizePaymentDetails($authorization_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->get_authorization($authorization_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['AUTHORIZATION']=$order->toArray();
            $returnArray['RAWREQUEST']='{authorization_id:'.$authorization_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function GetCapturedPaymentDetails($capture_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->get_capture($capture_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['CAPTURE']=$order->toArray();
            $returnArray['RAWREQUEST']='{capture_id:'.$capture_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function CaptureAuthorizedPayment($authorization_id,$requestBody){
        $orderObject = new CheckoutOrdersClass();
        $params = array_filter($requestBody);
        $requestArray = json_encode($params);
        try {
            $order = $orderObject->capture_authorization($authorization_id,$params,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['AUTHORIZATION']=$order->toArray();
            $returnArray['RAWREQUEST']=$requestArray;
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function Reauthorize($authorization_id,$requestBody){
        $orderObject = new CheckoutOrdersClass();
        $params = array_filter($requestBody);
        $requestArray = json_encode($params);
        try {
            $order = $orderObject->reauthorize($authorization_id,$params,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['AUTHORIZATION']=$order->toArray();
            $returnArray['RAWREQUEST']=$requestArray;
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function VoidAuthorizedPayment($authorization_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->void($authorization_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['AUTHORIZATION']=$order->toArray();
            $returnArray['RAWREQUEST']='{authorization_id:'.$authorization_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function RefundCapturedPayment($capture_id,$requestBody){
        $orderObject = new CheckoutOrdersClass();
        $params = array_filter($requestBody);
        $requestArray = json_encode($params);
        try {
            $order = $orderObject->refund($capture_id,$params,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['CAPTURE']=$order->toArray();
            $returnArray['RAWREQUEST']=$requestArray;
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }

    public function ShowRefundDetails($refund_id){
        $orderObject = new CheckoutOrdersClass();
        try {
            $order = $orderObject->get_refund_details($refund_id,$this->_api_context);
            $returnArray['RESULT'] = 'Success';
            $returnArray['REFUND']= $order->toArray();
            $returnArray['RAWREQUEST']='{refund_id:'.$refund_id.'}';
            $returnArray['RAWRESPONSE']=$order->toJSON();
            return $returnArray;
        } catch (\PayPal\Exception\PayPalConnectionException  $ex) {
            return $this->createErrorResponse($ex);
        }
    }
}
