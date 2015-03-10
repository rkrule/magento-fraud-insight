<?php
/**
 * Copyright (c) 2014 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */

class EbayEnterprise_RiskInsight_Model_Api
	implements EbayEnterprise_RiskInsight_Model_IApi
{
	/** @var EbayEnterprise_RiskInsight_Model_IConfig */
	protected $_config;
	/** @var EbayEnterprise_RiskInsight_Model_IPayload */
	protected $_requestPayload;
	/** @var  EbayEnterprise_RiskInsight_Model_IPayload */
	protected $_replyPayload;
	/** @var  Requests_Response Response object from the last call to Requests */
	protected $_lastRequestsResponse;

	/**
	 * Configure the api by supplying an object that informs
	 * what payload object to use, what URI to send to, etc.
	 *
	 * @param EbayEnterprise_RiskInsight_Model_Config $config
	 */
	public function __construct(EbayEnterprise_RiskInsight_Model_Config $config)
	{
		$this->_config = $config;
		Requests::register_autoloader();
	}

	public function setRequestBody(EbayEnterprise_RiskInsight_Model_IPayload $payload)
	{
		$this->_requestPayload = $payload;
		return $this;
	}

	public function send()
	{
		$this->getRequestBody()->serialize();

		// actually do the request
		try {
			if ($this->_sendRequest() === false) {
				$message = sprintf(
					"HTTP result %s for %s to %s.\n%s",
					$this->_lastRequestsResponse->status_code,
					$this->_config->getHttpMethod(),
					$this->_lastRequestsResponse->url,
					$this->_lastRequestsResponse->body
				);
				throw Mage::exception('EbayEnterprise_RiskInsight_Model_Exception_Network_Error', $message);
			}
		} catch (Requests_Exception $e) {
			// simply pass through the message but with an expected exception type - don't
			// have any request/response to include as this exception only occurs
			// when the request cannot even be attempted.
			throw Mage::exception('EbayEnterprise_RiskInsight_Model_Exception_Network_Error', $e->getMessage());
		}

		$responseData = $this->_lastRequestsResponse->body;
		$this->getResponseBody()->deserialize($responseData);

		return $this;
	}

	public function getRequestBody()
	{
		if ($this->_requestPayload !== null) {
			return $this->_requestPayload;
		}
		// If a payload doesn't exist for the request, the operation cannot
		// be supported.
		try {
			$this->_requestPayload = $this->_config->getRequest();
		} catch (EbayEnterprise_RiskInsight_Model_Exception_Unsupported_Payload_Exception $e) {
			throw Mage::exception('EbayEnterprise_RiskInsight_Model_Exception_Unsupported_Operation', '');
		}
		return $this->_requestPayload;
	}

	public function getResponseBody()
	{
		if ($this->_replyPayload !== null) {
			return $this->_replyPayload;
		}

		// If a payload doesn't exist for the response, the operation cannot
		// be supported.
		try {
			$this->_replyPayload = $this->_config->getResponse();
		} catch (EbayEnterprise_RiskInsight_Model_Exception_Unsupported_Payload_Exception $e) {
			throw Mage::exception('EbayEnterprise_RiskInsight_Model_Exception_Unsupported_Operation', '');
		}
		return $this->_replyPayload;
	}

	/**
	 * @return boolean
	 * @throws EbayEnterprise_RiskInsight_Model_Exception_Unsupported_Http_Action_Exception
	 */
	protected function _sendRequest()
	{
		// clear the old response
		$this->_lastRequestsResponse = null;
		$httpMethod = strtolower($this->_config->getHttpMethod());
		if (!method_exists($this, $httpMethod)) {
			throw Mage::exception(
				'EbayEnterprise_RiskInsight_Model_Exception_Unsupported_Http_Action',
				sprintf('HTTP action %s not supported.', strtoupper($httpMethod))
			);
		}

		return $this->$httpMethod();
	}

	/**
	 * @return Requests_Response
	 * @throws Requests_Exception
	 */
	protected function _post()
	{
		$this->_lastRequestsResponse = Requests::post(
			$this->_config->getEndpoint(),
			$this->_buildHeader(),
			$this->getRequestBody()->serialize()
		);
		return $this->_lastRequestsResponse->success;
	}

	protected function _buildHeader()
	{
		return [
			'apikey' => $this->_config->getApiKey(),
			'Content-type' => $this->_config->getContentType()
		];
	}

	/**
	 * @return Requests_Response
	 * @throws Requests_Exception
	 */
	protected function _get()
	{
		$this->_lastRequestsResponse = Requests::post(
			$this->_config->getEndpoint(),
			$this->_buildHeader()
		);
		return $this->_lastRequestsResponse->success;
	}
}
