<?php
/**
 * @filesource modules/inventory/models/inventory.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Inventory;

use Gcms\Login;
use Kotchasan\Http\Request;

/**
 * ค้นหา Inventory Autocomplete
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{

    /**
     * ฟังก์ชั่นค้นหาพัสดุจาก เลขพัสดุ
     *
     * @param Request $request
     *
     * @return JSON
     */
    public static function find(Request $request)
    {
        if ($request->initSession() && $request->isAjax() && Login::isMember()) {
            $result = static::createQuery()
                ->from('inventory V')
                ->join('inventory_items I', 'INNER', array('I.inventory_id', 'V.id'))
                ->where(array(
                    array('I.product_no', $request->post('value')->topic()),
                    array('V.inuse', 1),
                ))
                ->andWhere(array(
                    array('I.stock', '>', 0),
                    array('V.count_stock', 0),
                ), 'OR')
                ->order('V.topic', 'I.product_no')
                ->limit($request->post('count')->toInt())
                ->toArray()
                ->first('V.id', 'V.topic', 'I.product_no', 'I.unit', 'I.stock');
            if ($result) {
                echo json_encode($result);
            }
        }
    }

}
