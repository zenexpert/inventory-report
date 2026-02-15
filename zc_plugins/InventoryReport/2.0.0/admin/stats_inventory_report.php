<?php
/**
 * Zen Cart Inventory Report
 * Modernized for Zen Cart 2.1.0
 * @author damonparker.org <damonp@damonparker.org>
 * @author mprough PRO-Webs.net
 * @author ZenExpert https://zenexpert.com
 * @version 2026-02-15 ZenExpert
 */

require('includes/application_top.php');
require(DIR_WS_CLASSES . 'currencies.php');
$currencies = new currencies();

// Fallback for filename if not defined in extra_datafiles
$current_page = defined('FILENAME_STATS_INVENTORY_REPORT') ? FILENAME_STATS_INVENTORY_REPORT : basename(__FILE__);

// Variable assignment
$get_cat = isset($_GET['cat']) ? zen_db_prepare_input($_GET['cat']) : '';
$get_dir = isset($_GET['dir']) ? zen_db_prepare_input($_GET['dir']) : 'ASC';
$get_sort = isset($_GET['sort']) ? zen_db_prepare_input($_GET['sort']) : 'products_name';
$get_page = isset($_GET['page']) ? zen_db_prepare_input($_GET['page']) : '1';
$get_mfg = isset($_GET['mfg']) ? zen_db_prepare_input($_GET['mfg']) : '';
$status = isset($_GET['status']) ? zen_db_prepare_input($_GET['status']) : '';
$csv = isset($_GET['csv']) ? zen_db_prepare_input($_GET['csv']) : '0';
$active_only = isset($_GET['active_only']) ? (int)$_GET['active_only'] : 0;

$cat = ($get_cat == '0') ? '' : $get_cat;
$dir = ($get_dir == '') ? 'ASC' : $get_dir;
$mfg = $get_mfg;
$sort = ($get_sort == '') ? 'products_name' : $get_sort;

$where_array = array();
if ($cat != '') {
    $where_array[] = " p.master_categories_id = '" . (int)$cat . "' ";
}
if ($status != ''){
    $where_array[] = " p.products_status = '" . (int)$status . "' ";
}
if ($mfg != ''){
    $where_array[] = " p.manufacturers_id = '" . (int)$mfg . "' ";
}
if ($active_only == 1) {
    $where_array[] = " p.products_status = 1 ";
}

$db_category_where = (count($where_array) > 0) ? " WHERE " . implode(" AND ", $where_array) : '';

$op_dir = ($dir == 'ASC') ? 'DESC' : 'ASC';

// Initialize all sort direction variables
$dir_id = $dir_name = $dir_price = $dir_quantity = $dir_total = $dir_mfg_name = $dir_prdocts_min = $dir_model = 'DESC';

switch ($sort) {
    case('p.products_id'):
        $dir_id = $op_dir;
        break;
    case('products_name'):
        $dir_name = $op_dir;
        break;
    case('products_price'):
        $dir_price = $op_dir;
        break;
    case('products_quantity'):
        $dir_quantity = $op_dir;
        break;
    case('total'):
        $dir_total = $op_dir;
        break;
    case('m.manufacturers_name'):
        $dir_mfg_name = $op_dir;
        break;
    case('p.products_quantity_order_min'):
        $dir_prdocts_min = $op_dir;
        break;
    case('p.products_model'):
        $dir_model = $op_dir;
        break;
}

$lang_id = isset($_SESSION['languages_id']) ? (int)$_SESSION['languages_id'] : 1;

// Query updated to strictly adhere to MySQL ONLY_FULL_GROUP_BY standards
$products_query_raw = "SELECT p.products_id, p.products_quantity, pd.products_name, p.products_model, p.products_price, 
    (p.products_quantity * p.products_price) AS total, cd.categories_name, p.products_quantity_order_min, m.manufacturers_name, p.products_status 
    FROM " . TABLE_PRODUCTS . " p 
    LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON (pd.products_id = p.products_id AND pd.language_id = " . $lang_id . ") 
    LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON (cd.categories_id = p.master_categories_id AND cd.language_id = " . $lang_id . ") 
    LEFT JOIN " . TABLE_MANUFACTURERS . " m ON (m.manufacturers_id = p.manufacturers_id) 
    " . $db_category_where . " 
    GROUP BY p.products_id, p.products_quantity, pd.products_name, p.products_model, p.products_price, cd.categories_name, p.products_quantity_order_min, m.manufacturers_name, p.products_status 
    ORDER BY " . $sort . " " . $dir;

// Handle CSV Export
if ($csv == '1') {
    ob_end_clean();
    $current_inventory = $db->Execute($products_query_raw);
    $products = array();
    while (!$current_inventory->EOF) {
        $products[] = array(
                'products_id' => $current_inventory->fields['products_id'],
                'products_model' => $current_inventory->fields['products_model'],
                'products_name' => $current_inventory->fields['products_name'],
                'categories_name' => $current_inventory->fields['categories_name'],
                'manufacturers_name' => $current_inventory->fields['manufacturers_name'],
                'status' => $current_inventory->fields['products_status'],
                'products_quantity' => $current_inventory->fields['products_quantity'],
                'products_quantity_order_min' => $current_inventory->fields['products_quantity_order_min'],
                // Strip tags and decode HTML entities for CSV output
                'products_price' => html_entity_decode(strip_tags($currencies->format($current_inventory->fields['products_price'])), ENT_QUOTES, 'UTF-8'),
                'total' => html_entity_decode(strip_tags($currencies->format($current_inventory->fields['total'])), ENT_QUOTES, 'UTF-8'),
        );
        $current_inventory->MoveNext();
    }

    if (count($products) > 0) {
        $filename = 'inventory_report_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        $out = fopen('php://output', 'w');
        fputcsv($out, array_keys($products['0']));
        foreach ($products as $product) {
            fputcsv($out, $product);
        }
        fclose($out);
    }
    die();
}
?>
    <!doctype html>
    <html <?= HTML_PARAMS ?>>
    <head>
        <?php require DIR_WS_INCLUDES . 'admin_html_head.php'; ?>
    </head>
    <body>
    <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-xs-12">
                <h1 class="pageHeading"><?= HEADING_TITLE ?></h1>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-sm-6">
                <a href="<?php echo zen_href_link($current_page, 'page=all&sort=' . $sort . '&dir=' . $dir . '&cat=' . $cat); ?>" class="btn btn-default"><?= TEXT_SHOW_ALL ?></a>
                <a href="<?php echo zen_href_link($current_page, 'sort=' . $sort . '&dir=' . $dir . '&cat=' . $cat); ?>" class="btn btn-default"><?= TEXT_PAGINATE ?></a>

                <?php if ($active_only == 1) { ?>
                    <a href="<?= zen_href_link($current_page, 'active_only=0&sort=' . $sort . '&dir=' . $dir . '&cat=' . $cat . ($get_page == 'all' ? '&page=all' : '')) ?>" class="btn btn-warning"><?= TEXT_DISABLED_ITEMS_ON ?></a>
                <?php } else { ?>
                    <a href="<?= zen_href_link($current_page, 'active_only=1&sort=' . $sort . '&dir=' . $dir . '&cat=' . $cat . ($get_page == 'all' ? '&page=all' : '')) ?>" class="btn btn-info"><?= TEXT_DISABLED_ITEMS_OFF ?></a>
                <?php } ?>
            </div>
            <div class="col-sm-6 text-right">
                <?php
                echo zen_draw_form('cat_filter', $current_page, 'get', 'class="form-inline"');
                echo zen_draw_hidden_field('cmd', $current_page);
                echo zen_draw_hidden_field('active_only', $active_only)
                ?>
                <div class="form-group">
                    <?= zen_draw_pull_down_menu('cat', zen_get_category_tree(), $cat, 'class="form-control" onChange="this.form.submit();"') ?>
                </div>
                <?= zen_hide_session_id() ?>
                <a href="<?= zen_href_link($current_page, zen_get_all_get_params(array('page', 'csv')) . 'csv=1') ?>" class="btn btn-success"><?= INVENTORY_REPORT_TEXT_CSV ?></a>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead>
                <tr class="dataTableHeadingRow">
                    <th class="text-center"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_id.'&sort=p.products_id') ?>"><?= TABLE_HEADING_NUMBER ?></a></th>
                    <th class="text-center"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_model.'&sort=p.products_model') ?>"><?= TABLE_HEADING_MODEL ?></a></th>
                    <th><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_name.'&sort=products_name'); ?>"><?= TABLE_HEADING_PRODUCTS ?></a></th>
                    <th class="text-center"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_name.'&sort=cd.categories_name') ?>"><?= TABLE_HEADING_MASTER_CATEGORY ?></a></th>
                    <th class="text-center"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_mfg_name.'&sort=m.manufacturers_name') ?>"><?= TABLE_HEADING_MANUFACTURER ?></a></th>
                    <?php if ($active_only == 0) { ?>
                        <th class="text-center"><?= TABLE_HEADING_STATUS ?></th>
                    <?php } ?>
                    <th class="text-center"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_quantity.'&sort=products_quantity') ?>"><?= TABLE_HEADING_QUANTITY ?></a></th>
                    <th class="text-center"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_prdocts_min.'&sort=p.products_quantity_order_min'); ?>"><?= TABLE_HEADING_MINIMUM_QUANTITY ?></a></th>
                    <th class="text-right"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_price.'&sort=products_price') ?>"><?= TABLE_HEADING_PRICE ?></a></th>
                    <th class="text-right"><a href="<?= zen_href_link($current_page, 'page='.$get_page.'&cat='.$cat.'&dir='.$dir_total.'&sort=total') ?>"><?= TABLE_HEADING_TOTAL ?></a></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $rows = 0;
                $total = 0;
                $products_query_numrows = '';

                if ($get_page != 'all') {
                    $products_split = new splitPageResults($get_page, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $products_query_raw, $products_query_numrows);
                }
                $products = $db->Execute($products_query_raw);

                foreach ($products as $product) {
                    $rows++;
                    $cPath = zen_get_product_path($product['products_id']);
                    $total += $product['total'];
                    ?>
                    <tr style="cursor: pointer;" onclick="document.location.href = '<?= zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $product['products_id']) ?>'">
                        <td class="text-center"><?= $product['products_id'] ?></td>
                        <td class="text-center"><?= $product['products_model'] ?></td>
                        <td><a href="<?= zen_href_link(FILENAME_CATEGORY_PRODUCT_LISTING, 'cPath=' . $cPath . '&pID=' . $product['products_id']) ?>"><?= $product['products_name'] ?></a></td>
                        <td class="text-center"><?= $product['categories_name'] ?></td>
                        <td class="text-center"><?= $product['manufacturers_name'] ?></td>
                        <?php if ($active_only == 0) { ?>
                            <td class="text-center">
                                <?php if ($product['products_status'] == 1) { ?>
                                    <i class="fa fa-check fa-lg text-success" title="<?= TEXT_STATUS_ACTIVE ?>" aria-label="<?= TEXT_STATUS_ACTIVE ?>"></i>
                                    <span class="sr-only"><?= TEXT_STATUS_ACTIVE ?></span>
                                <?php } else { ?>
                                    <i class="fa fa-times fa-lg text-danger" title="<?= TEXT_STATUS_INACTIVE ?>" aria-label="<?= TEXT_STATUS_INACTIVE ?>"></i>
                                    <span class="sr-only"><?= TEXT_STATUS_INACTIVE ?></span>
                                <?php } ?>
                            </td>
                        <?php } ?>
                        <td class="text-center <?= ($product['products_quantity'] <= 0) ? 'danger' : '' ?>"><?= $product['products_quantity'] ?></td>
                        <td class="text-center"><?= $product['products_quantity_order_min'] ?></td>
                        <td class="text-right"><?= $currencies->format($product['products_price']) ?></td>
                        <td class="text-right"><?= $currencies->format($product['total']) ?></td>
                    </tr>
                <?php } ?>

                </tbody>
                <tfoot>
                <tr class="info">
                    <td colspan="<?= ($active_only == 0) ? '9' : '8' ?>" class="text-right"><strong><?= TEXT_PAGE_TOTAL ?></strong></td>
                    <td class="text-right"><strong><?= $currencies->format($total) ?></strong></td>
                </tr>
                </tfoot>
            </table>
        </div>

        <?php if (isset($products_split) && is_object($products_split)) { ?>
            <div class="row">
                <div class="col-sm-6 text-muted">
                    <?php echo $products_split->display_count($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, $get_page, TEXT_DISPLAY_NUMBER_OF_PRODUCTS); ?>
                </div>
                <div class="col-sm-6 text-right">
                    <?= $products_split->display_links($products_query_numrows, MAX_DISPLAY_SEARCH_RESULTS_REPORTS, MAX_DISPLAY_PAGE_LINKS, $get_page, "sort=$sort&dir=$dir&cat=$cat&active_only=$active_only") ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
    </body>
    </html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>
