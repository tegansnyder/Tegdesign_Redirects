<?php
/**
 * Tegdesign_Redirects
 *
 * The purpose of this extension is to prevent 404s on products
 * and categorys when suffixes are configured in Magento.
 * Magento EE 1.13.0.1 had a bug that didn't honor the 
 * SEO suffixes configured. Allowing you to visit a page with or
 * without the suffix that was configured. We upgrading from 1.13.0.1
 * to newer versions of Magento EE 1.14 you may encounter 404 issues
 * on links you had configured without the suffix. This extension allows
 * your categories and products to be access with and without the suffix.
 *
 * Suffix are configured to allow you to access products via
 * www.domain.com/product.html
 * However with them enabled you will 404 when you try and run
 * www.domain.com/product
 *
 * The sole function of this extension to prevent edge cases and
 * do a 301 redirect to the proper url_key + suffix for
 * a product or category accessed without a suffix when suffixes
 * are configured in System -> Configuration -> Search Engine Optimizations 
 *
 * @category    Tegdesign
 * @package     Redirects
 * @author      Tegan Snyder <tsnyder@tegdesign.com>
 */

include_once('Mage/Cms/controllers/IndexController.php');

class Tegdesign_Redirects_Cms_IndexController extends Mage_Cms_IndexController {

    /**
     * This checks to see if the site can redirect
     * before displaying a 404
     * @param type $coreRoute
     */
    public function noRouteAction($coreRoute = null) {

        // check if suffix redirection is enabled in System -> Configuration -> Advanced
        if (Mage::getStoreConfig('tegdesign_redirects/suffix_redirect/enabled')) {

            // grab the url suffix from system -> config
            $product_url_suffix = Mage::getStoreConfig('catalog/seo/product_url_suffix');
            $category_url_suffix = Mage::getStoreConfig('catalog/seo/category_url_suffix');

            $request = $_SERVER['REQUEST_URI'];
            $params = $_SERVER['QUERY_STRING'];

            // strip any params from request
            if (strpos($request, '?') !== FALSE) {
                $request = substr($request, 0, strpos($request, '?'));
            }

            /**
             *  This makes sure that no part of the request is included
             *  in the base url. This should help
             *  the system work if it is running from a sub folder
             */
            $parts = explode('/', Mage::getBaseUrl());
            foreach ($parts AS $part) {
                $request = str_replace(array($part, '//'), '', $request);
            }

    		/*
             * Combine the request with the base path to get the full url
             * and remove any trailing slashes
             */
            $path = trim(str_replace('http:/', 'http://', str_replace('//', '/', Mage::getBaseUrl() . $request)), '/');
            $cleanedPath = explode('/', $path);
            $key = array_pop($cleanedPath);

            // remove trailing slash
            $request = rtrim($request, "/");

            /*
             * Determine if the URL entered is a product
             * by forming a LIKE query against the product collection
             */
            $collection = Mage::getModel('catalog/product')->getCollection();
            $collection->addAttributeToSelect('url_key');
            $collection->addFieldToFilter(array(
                array('attribute' => 'url_key', 'like' => "$key")));
            $products = $collection->load();
            
            /*
             * Proceed if there is only 1 result, if there are more
             * we don't know which one is correct
             */
            if (count($products) == 1) {

            	// append the suffix
            	$request = $request . '.' . $product_url_suffix;
                if ($_SERVER['QUERY_STRING'] !== '') {
                    $request = $request . '?' . $params;
                }

            	header('HTTP/1.1 301 Moved Permanently');
                header('Location: ' . $request);
                die();

            } else {
                /*
                 * Determine if the URL entered is a category
                 * by forming a LIKE query against the category collection
                 */
                $collection = Mage::getModel('catalog/category')->getCollection();
                $collection->addAttributeToSelect('url_key');
                $collection->addFieldToFilter(array(
                    array('attribute' => 'url_key', 'like' => "$key")));
                $categorys = $collection->load();

                if (count($categorys) == 1) {

                    // append the suffix
                    $request = $request . '.' . $category_url_suffix;
                    if ($_SERVER['QUERY_STRING'] !== '') {
                        $request = $request . '?' . $params;
                    }

                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . $request);
                    die();

                }

            }

        } // end if enabled

        // could not find a suitable url carry out the 404 redirect
        parent::noRouteAction($coreRoute);
    }

}