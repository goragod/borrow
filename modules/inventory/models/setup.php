<?php
/**
 * @filesource modules/inventory/models/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Inventory\Setup;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params)
    {
        $where = array();
        if ($params['category_id'] > 0) {
            $where[] = array('V.category_id', $params['category_id']);
        }
        if ($params['model_id'] > 0) {
            $where[] = array('V.model_id', $params['model_id']);
        }
        if ($params['type_id'] > 0) {
            $where[] = array('V.type_id', $params['type_id']);
        }
        return static::createQuery()
            ->select('V.id', 'V.topic', 'I.product_no', 'V.category_id', 'V.type_id', 'V.model_id', 'I.stock', 'I.unit', 'V.inuse')
            ->from('inventory V')
            ->join('inventory_items I', 'LEFT', array('I.inventory_id', 'V.id'))
            ->where($where);
    }

    /**
     * รับค่าจาก action (setup.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = array();
        // session, referer, can_manage_inventory, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login) && Login::checkPermission($login, 'can_manage_inventory')) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString();
                // Database
                $db = $this->db();
                // id ที่ส่งมา
                if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->toString(), $match)) {
                    if ($action === 'delete') {
                        // ลบ
                        $db->delete($this->getTableName('inventory'), array('id', $match[1]), 0);
                        $db->delete($this->getTableName('inventory_meta'), array('inventory_id', $match[1]), 0);
                        $db->delete($this->getTableName('inventory_items'), array('inventory_id', $match[1]), 0);
                        // ลบรูปภาพ
                        $dir = ROOT_PATH.DATA_FOLDER.'inventory/';
                        foreach ($match[1] as $id) {
                            if (is_file($dir.$id.'.jpg')) {
                                unlink($dir.$id.'.jpg');
                            }
                        }
                        // reload
                        $ret['location'] = 'reload';
                    } elseif ($action == 'inuse') {
                        // สถานะ
                        $table = $this->getTableName('inventory');
                        $search = $db->first($table, (int) $match[1][0]);
                        if ($search) {
                            $inuse = $search->inuse == 1 ? 0 : 1;
                            $db->update($table, $search->id, array('inuse' => $inuse));
                            // reload
                            $ret['location'] = 'reload';
                        }
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }
}
