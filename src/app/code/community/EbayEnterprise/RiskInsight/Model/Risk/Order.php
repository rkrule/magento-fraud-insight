<?php
/**
 * Copyright (c) 2015 eBay Enterprise, Inc.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the eBay Enterprise
 * Magento Extensions End User License Agreement
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf
 *
 * @copyright   Copyright (c) 2015 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://www.ebayenterprise.com/files/pdf/Magento_Connect_Extensions_EULA_050714.pdf  eBay Enterprise Magento Extensions End User License Agreement
 *
 */

class EbayEnterprise_RiskInsight_Model_Risk_Order
	implements EbayEnterprise_RiskInsight_Model_Risk_IOrder
{
	/** @var EbayEnterprise_RiskInsight_Helper_Data */
	protected $_helper;
	/** @var EbayEnterprise_RiskInsight_Helper_Config */
	protected $_config;

	/**
	 * @param array $initParams Must have this key:
	 *                          - 'helper' => EbayEnterprise_RiskInsight_Helper_Data
	 *                          - 'config' => EbayEnterprise_RiskInsight_Helper_Config
	 */
	public function __construct(array $initParams=array())
	{

		list($this->_helper, $this->_config) = $this->_checkTypes(
			$this->_nullCoalesce($initParams, 'helper', Mage::helper('ebayenterprise_riskinsight')),
			$this->_nullCoalesce($initParams, 'config', Mage::helper('ebayenterprise_riskinsight/config'))
		);
	}

	/**
	 * Type hinting for self::__construct $initParams
	 *
	 * @param  EbayEnterprise_RiskInsight_Helper_Data
	 * @param  EbayEnterprise_RiskInsight_Helper_Config
	 * @return array
	 */
	protected function _checkTypes(
		EbayEnterprise_RiskInsight_Helper_Data $helper,
		EbayEnterprise_RiskInsight_Helper_Config $config
	) {
		return array($helper, $config);
	}

	/**
	 * Return the value at field in array if it exists. Otherwise, use the default value.
	 *
	 * @param  array
	 * @param  string | int $field Valid array key
	 * @param  mixed
	 * @return mixed
	 */
	protected function _nullCoalesce(array $arr, $field, $default)
	{
		return isset($arr[$field]) ? $arr[$field] : $default;
	}

	/**
	 * Get new empty request payload
	 *
	 * @return EbayEnterprise_RiskInsight_Model_IPayload
	 */
	protected function _getNewEmptyRequest()
	{
		return Mage::getModel('ebayenterprise_riskinsight/request');
	}

	/**
	 * Get new empty response payload
	 *
	 * @return EbayEnterprise_RiskInsight_Model_IPayload
	 */
	protected function _getNewEmptyResponse()
	{
		return Mage::getModel('ebayenterprise_riskinsight/response');
	}

	/**
	 * Get new API config object.
	 *
	 * @param  EbayEnterprise_RiskInsight_Model_IPayload
	 * @param  EbayEnterprise_RiskInsight_Model_IPayload
	 * @return EbayEnterprise_RiskInsight_Model_IConfig
	 */
	protected function _setupApiConfig(
		EbayEnterprise_RiskInsight_Model_IPayload $request,
		EbayEnterprise_RiskInsight_Model_IPayload $response
	)
	{
		return Mage::getModel('ebayenterprise_riskinsight/config', array(
			'api_key' => $this->_config->getApiKey(),
			'host' => $this->_config->getApiHostname(),
			'store_id' => $this->_config->getStoreId(),
			'request' => $request,
			'response' => $response,
		));
	}

	/**
	 * Get new API object.
	 *
	 * @return EbayEnterprise_RiskInsight_Model_IApi
	 */
	protected function _getApi(EbayEnterprise_RiskInsight_Model_IConfig $config)
	{
		return Mage::getModel('ebayenterprise_riskinsight/api', $config);
	}

	public function process()
	{
		$this->_processRiskOrderCollection($this->_helper->getRiskInsightCollection());
		return $this;
	}

	public function processRiskOrder(
		EbayEnterprise_RiskInsight_Model_Risk_Insight $insight,
		Mage_Sales_Model_Order $order
	)
	{
		$fufillRequest = $this->_buildRequestFromOrder($this->_getNewEmptyRequest(), $insight, $order);
		$apiConfig = $this->_setupApiConfig($fufillRequest, $this->_getNewEmptyResponse());
		$response = $this->_sendRequest($this->_getApi($apiConfig));
		if ($response) {
			$this->_processResponse($response, $insight, $order);
		}
	}

	/**
	 * @param  EbayEnterprise_RiskInsight_Model_Resource_Risk_Insight_Collection
	 * @return self
	 */
	protected function _processRiskOrderCollection(EbayEnterprise_RiskInsight_Model_Resource_Risk_Insight_Collection $insightCollection)
	{
		$orderCollection = $this->_helper->getOrderCollectionByIncrementIds($this->_getOrderIncementIds($insightCollection));
		foreach ($insightCollection as $insight) {
			$order = $orderCollection->getItemByColumnValue('increment_id', $insight->getOrderIncrementId());
			if ($order) {
				$this->processRiskOrder($insight, $order);
			}
		}
		return $this;
	}

	/**
	 * @param  EbayEnterprise_RiskInsight_Model_IApi
	 * @return EbayEnterprise_RiskInsight_Model_IPayload | null
	 */
	protected function _sendRequest(EbayEnterprise_RiskInsight_Model_IApi $api)
	{
		$response = null;
		try {
			$api->send();
			$response = $api->getResponseBody();
		} catch (Exception $e) {
			$logMessage = sprintf('[%s] The following error has occurred while sending request: %s', __CLASS__, $e->getMessage());
			Mage::log($logMessage, Zend_Log::WARN);
			Mage::logException($e);
		}
		return $response;
	}

	/**
	 * @param  EbayEnterprise_RiskInsight_Model_IPayload
	 * @param  EbayEnterprise_RiskInsight_Model_Risk_Insight
	 * @param  Mage_Sales_Model_Order $order
	 * @return self
	 */
	protected function _processResponse(
		EbayEnterprise_RiskInsight_Model_IPayload $response,
		EbayEnterprise_RiskInsight_Model_Risk_Insight $insight,
		Mage_Sales_Model_Order $order
	)
	{
		return Mage::getModel('ebayenterprise_riskinsight/process_response', array(
			'response' => $response,
			'insight' => $insight,
			'order' => $order,
		))->process();

		return $this;
	}

	/**
	 * Build the passed in request object using the passed in order and insight object.
	 *
	 * @param  EbayEnterprise_RiskInsight_Model_IPayload
	 * @param  EbayEnterprise_RiskInsight_Model_Risk_Insight
	 * @param  Mage_Sales_Model_Order
	 * @return EbayEnterprise_RiskInsight_Model_IPayload
	 */
	protected function _buildRequestFromOrder(
		EbayEnterprise_RiskInsight_Model_IPayload $request,
		EbayEnterprise_RiskInsight_Model_Risk_Insight $insight,
		Mage_Sales_Model_Order $order
	)
	{
		return Mage::getModel('ebayenterprise_riskinsight/build_request', array(
			'request' => $request,
			'insight' => $insight,
			'order' => $order,
		))->build();
	}

	/**
	 * Get all order increment id from a passed in collection of risk insight instance.
	 *
	 * @param  EbayEnterprise_RiskInsight_Model_Resource_Risk_Insight_Collection
	 * @return array
	 */
	protected function _getOrderIncementIds(EbayEnterprise_RiskInsight_Model_Resource_Risk_Insight_Collection $collections)
	{
		$incrementIds = array();
		foreach ($collections as $insight) {
			$incrementIds[] = $insight->getOrderIncrementId();
		}
		return $incrementIds;
	}
}
