<?php
/**
 * @filesource modules/borrow/models/report.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Borrow\Report;

use Kotchasan\Database\Sql;

/**
 * module=borrow-report
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
        $where = array(
            array('S.status', $params['status']),
        );
        if (!empty($params['borrower_id'])) {
            $where[] = array('W.borrower_id', $params['borrower_id']);
        }
        $query = static::createQuery()
            ->select('S.borrow_id', 'S.id', 'W.borrow_no', 'S.product_no', 'S.topic', 'I.stock', 'S.num_requests', 'W.borrow_date',
                'W.return_date', 'U.name borrower', 'U.status Ustatus', 'W.borrower_id', 'S.amount', 'V.count_stock',
                Sql::DATEDIFF('W.return_date', date('Y-m-d'), 'due'), 'S.status')
            ->from('borrow W')
            ->join('borrow_items S', 'INNER', array('S.borrow_id', 'W.id'))
            ->join('inventory_items I', 'INNER', array('I.product_no', 'S.product_no'))
            ->join('inventory V', 'INNER', array('V.id', 'I.inventory_id'))
            ->join('user U', 'LEFT', array('U.id', 'W.borrower_id'))
            ->where($where);
        if ($params['status'] == 2) {
            if ($params['due'] == 1) {
                $query->andWhere(array(
                    array(Sql::DATEDIFF('W.return_date', date('Y-m-d')), '<=', 0),
                ));
            } else {
                $query->andWhere(array(
                    array(Sql::DATEDIFF('W.return_date', date('Y-m-d')), '>', 0),
                    Sql::ISNULL('W.return_date'),
                ), 'OR');
            }
        }
        return $query;
    }
}
