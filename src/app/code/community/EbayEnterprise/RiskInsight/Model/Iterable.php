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

abstract class EbayEnterprise_RiskInsight_Model_Iterable
	extends SPLObjectStorage
	implements EbayEnterprise_RiskInsight_Model_IIterable
{
	/** @var EbayEnterprise_RiskInsight_Helper_Data */
	protected $_helper;
	/** @var bool */
	protected $_includeIfEmpty = false;
	/** @var bool */
	protected $_buildRootNode = true;

	/**
	 * @param array $initParams Must have this key:
	 *                          - 'helper' => EbayEnterprise_RiskInsight_Helper_Data
	 */
	public function __construct(array $initParams=array())
	{
		$this->_helper = $this->_checkHelperClassType(
			$this->_nullCoalesce($initParams, 'helper', Mage::helper('ebayenterprise_riskinsight'))
		);
	}

	/**
	 * Type hinting for self::__construct $initParams
	 *
	 * @param  EbayEnterprise_RiskInsight_Helper_Data
	 * @return EbayEnterprise_RiskInsight_Helper_Data
	 */
	protected function _checkHelperClassType(EbayEnterprise_RiskInsight_Helper_Data $helper)
	{
		return $helper;
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

	public function serialize()
	{
		$format = $this->_buildRootNode ? '<%1$s>%2$s</%1$s>' : '%2$s';
		$serializedSubpayloads = $this->_serializeContents();
		return ($this->_includeIfEmpty || $serializedSubpayloads)
			? sprintf($format, $this->_getRootNodeName(), $serializedSubpayloads)
			: '';
	}

	public function deserialize($serializedData)
	{
		$xpath = $this->_helper->getPayloadAsXPath($serializedData, $this->_getXmlNamespace());
		foreach ($xpath->query($this->_getSubpayloadXPath()) as $subpayloadNode) {
			$pl = $this->_getNewSubpayload()->deserialize($subpayloadNode->C14N());
			$this->offsetSet($pl);
		}
		return $this;
	}

	protected function _serializeContents()
	{
		$serializedSubpayloads = '';
		foreach ($this as $subpayload) {
			$serializedSubpayloads .= $subpayload->serialize();
		}
		return $serializedSubpayloads;
	}

	/**
	 * Get an XPath expression that will separate the serialized data into
	 * XML for each subpayload in the iterable.
	 *
	 * @return string
	 */
	abstract protected function _getSubpayloadXPath();

	/**
	 * Build a new IPayload for the given interface.
	 *
	 * @param string
	 * @return IPayload
	 */
	protected function _buildPayloadForModel($model)
	{
		return Mage::getModel($model);
	}

	/**
	 * XML Namespace of the document.
	 *
	 * @return string
	 */
	abstract protected function _getXmlNamespace();
}
