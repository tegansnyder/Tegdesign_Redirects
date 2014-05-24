The purpose of this extension is to prevent 404s on products
and categorys when suffixes are configured in Magento.
Magento EE 1.13.0.1 had a bug that didn't honor the 
SEO suffixes configured. Allowing you to visit a page with or
without the suffix that was configured. We upgrading from 1.13.0.1
to newer versions of Magento EE 1.14 you may encounter 404 issues
on links you had configured without the suffix. This extension allows
your categories and products to be access with and without the suffix.

Suffix are configured to allow you to access products via
www.domain.com/product.html
However with them enabled you will 404 when you try and run
www.domain.com/product

The sole function of this extension to prevent edge cases and
do a 301 redirect to the proper url_key + suffix for
a product or category accessed without a suffix when suffixes
are configured in System -> Configuration -> Search Engine Optimizations 