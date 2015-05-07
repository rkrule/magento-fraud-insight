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

interface EbayEnterprise_RiskInsight_Sdk_Line_IItem extends EbayEnterprise_RiskInsight_Sdk_IPayload
{
	const ROOT_NODE = 'LineItem';
	const XML_NS = 'http://schema.gsicommerce.com/risk/insight/1.0/';

	/**
	 * @return string
	 */
	public function getLineItemId();

	/**
	 * @param  string
	 * @return self
	 */
	public function setLineItemId($lineItemId);

	/**
	 * @return string
	 */
	public function getShipmentId();

	/**
	 * @param  string
	 * @return self
	 */
	public function setShipmentId($shipmentId);

	/**
	 * @return string
	 */
	public function getProductId();

	/**
	 * @param  string
	 * @return self
	 */
	public function setProductId($productId);

	/**
	 * @return string
	 */
	public function getDescription();

	/**
	 * @param  string
	 * @return self
	 */
	public function setDescription($description);

	/**
	 * @return float
	 */
	public function getUnitCost();

	/**
	 * @param  float
	 * @return self
	 */
	public function setUnitCost($unitCost);

	/**
	 * @return string
	 */
	public function getUnitCurrencyCode();

	/**
	 * @param  string
	 * @return self
	 */
	public function setUnitCurrencyCode($unitCurrencyCode);

	/**
	 * @return int
	 */
	public function getQuantity();

	/**
	 * @param  int
	 * @return self
	 */
	public function setQuantity($quantity);

	/**
	 * @return string
	 */
	public function getCategory();

	/**
	 * @param  string
	 * @return self
	 */
	public function setCategory($category);

	/**
	 * @return string
	 */
	public function getPromoCode();

	/**
	 * @param  string
	 * @return self
	 */
	public function setPromoCode($promoCode);
}