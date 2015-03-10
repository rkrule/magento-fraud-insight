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

interface EbayEnterprise_RiskInsight_Model_ITotal extends EbayEnterprise_RiskInsight_Model_IPayload
{
	const ROOT_NODE = 'TotalCost';
	const XML_NS = 'http://schema.gsicommerce.com/risk/insight/1.0/';
	const COST_TOTALS_MODEL ='ebayenterprise_riskinsight/cost_totals';

	/**
	 * Contains the total cost details regarding currency used, before tax amount, and after tax amount.
	 *
	 * @return EbayEnterprise_RiskInsight_Model_Cost_ITotals
	 */
	public function getCostTotals();
	/**
	 * @param  EbayEnterprise_RiskInsight_Model_Cost_ITotals $costTotals
	 * @return self
	 */
	public function setCostTotals(EbayEnterprise_RiskInsight_Model_Cost_ITotals $costTotals);
}
