<?php
class ProductParser {
    private $feedsUrl = 'https://wp.webspark.dev/wp-api/products';
    private $productJsonName = 'productsJson.json';
    public $parse_status = '';
    public $parsed_product_counter = 0;
    public $uploadDir = WPSYNC_WEBSPARK__PLUGIN_DIR.'/product-json';

    public function __construct()
    {
        $this->checkParseStatuses();
        $this->parse_status = get_option('parse_status');
        $this->parsed_product_counter = get_option('parsed_product_counter');
    }

    public function init() {
        if ($this->parse_status == 'start') {
            $this->createOrUpdateProductJson();
        }
        if ($this->parse_status == 'file_created' || $this->parse_status == 'in_process') {
            $this->parseProductsProcess();
        }
    }

    public function createOrUpdateProductJson() {
        $data   = file_get_contents($this->feedsUrl);
        $decoded_data = json_decode($data);
        $parse_status = $this->parse_status;

        if ($data && !$decoded_data->error && $parse_status != 'in_process') {
            $ifp = @fopen( $this->uploadDir.'/'.$this->productJsonName, 'wb' );
            if ( ! $ifp ) {
                return array(
                    'error' => sprintf( __( 'Could not write file %s' ), $this->uploadDir.'/'.$this->productJsonName ),
                );
            }

            fwrite( $ifp, $data );
            fclose( $ifp );

            $this->updateParseStatus('file_created', 0);

            return true;
        } elseif ($parse_status != 'in_process') {
            $this->createOrUpdateProductJson();
        }

        return false;
    }

    public function parseProductsProcess() {
        $productData = $this->getParseProducts();
        $parsed_product_counter = $this->parsed_product_counter;

        $productData = array_slice($productData, $parsed_product_counter);
        if ($productData) {
            foreach ($productData as $product) {
                $existProductID = wc_get_product_id_by_sku($product->sku);

                if ($existProductID) {
                    $this->updateProduct($existProductID, $product);
                } else {
                    $this->insertProduct($product);
                }

                $parsed_product_counter++;
                $this->updateParseStatus('in_process', $parsed_product_counter );
            }
        } else {
            $this->deleteProducts();
            $this->updateParseStatus('completed', $parsed_product_counter );
        }
    }

    public function getParseProducts() {
        if (file_exists($this->uploadDir.'/'.$this->productJsonName)) {
            $productData = file_get_contents($this->uploadDir.'/'.$this->productJsonName);
            $productData = json_decode($productData);
            return $productData->data;
        } else {
            $this->createOrUpdateProductJson();
            $this->getParseProducts();
        }

        return [];
    }

    public function checkParseStatuses() {
        if (!get_option('parse_status'))
            add_option('parse_status', 'start');

        if (!get_option('parsed_product_counter'))
            add_option('parsed_product_counter', 0);
    }

    public function updateParseStatus($parse_status, $parsed_product_counter = false) {
        update_option('parse_status', $parse_status);
        $this->parse_status = $parse_status;

        if ($parsed_product_counter !== false) {
            update_option('parsed_product_counter', $parsed_product_counter);
            $this->parsed_product_counter = $parsed_product_counter;
        }
    }

    public function deleteProducts() {
        $productData = $this->getParseProducts();

        if (!empty($productData)) {
            $allProductIds = get_posts( array(
                'post_type' => 'product',
                'numberposts' => -1,
                'fields' => 'ids',
                'post_status' => 'publish'
            ) );

            $productsSKU = array_map(fn($product): string => $product->sku, $productData );

            foreach ($allProductIds as $productId) {
                $productSKU = get_post_meta($productId, '_sku', true);
                if(!in_array($productSKU, $productsSKU)) {
                    $wcProduct = wc_get_product($productId);
                    $wcProduct->delete(true);
                }
            }
        }
    }

    public function insertProduct($product) {
        $wcProduct = new WC_Product_Simple();
        $wcProduct->set_name($product->name);
        $wcProduct->set_sku($product->sku);
        $wcProduct->set_description($product->description);
        $wcProduct->set_stock_quantity($product->in_stock);
        $wcProduct->set_price($product->price);
        $wcProduct->set_regular_price($product->price);
        $product_id = $wcProduct->save();
        fifu_dev_set_image($product_id, $product->picture);
    }

    public function updateProduct($product_id, $newProduct) {
        $wcProduct = new WC_Product_Simple($product_id);
        $wcProduct->set_name($newProduct->name);
        $wcProduct->set_description($newProduct->description);
        $wcProduct->set_stock_quantity($newProduct->in_stock);
        $wcProduct->set_price($newProduct->price);
        $wcProduct->set_regular_price($newProduct->price);
        $product_id = $wcProduct->save();
        fifu_dev_set_image($product_id, $newProduct->picture);
    }
}