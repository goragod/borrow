<?php
/**
 * @filesource modules/borrow/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license http://www.kotchasan.com/license/
 *
 * @see http://www.kotchasan.com/
 */

namespace Borrow\Email;

use Kotchasan\Date;
use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ส่งอีเมลแจ้งการทำรายการ
     *
     * @param int $id
     */
    public static function send($id)
    {
        // ตรวจสอบรายการที่ต้องการ
        $order = \Kotchasan\Model::createQuery()
            ->from('borrow B')
            ->join('user U', 'LEFT', array('U.id', 'B.borrower_id'))
            ->where(array('B.id', $id))
            ->first('B.borrow_no', 'B.transaction_date', 'B.borrow_date', 'B.return_date', 'U.name', 'U.username');
        if ($order) {
            $ret = array();
            // ข้อความ
            $content = array(
                '{LNG_Borrow} & {LNG_Return} '.$order->borrow_no,
                '{LNG_Borrower} '.$order->name,
                '{LNG_Transaction date} '.Date::format($order->transaction_date, 'd M Y'),
                '{LNG_Borrowed date} '.Date::format($order->borrow_date, 'd M Y'),
                '{LNG_Date of return} '.Date::format($order->return_date, 'd M Y'),
            );
            foreach (\Borrow\Order\Model::items($id) as $item) {
                $content[] = $item['topic'].' ['.$item['product_no'].'] '.$item['num_requests'].' '.$item['unit'].' ('.Language::find('BORROW_STATUS', null, $item['status']).')';
            }
            $msg = Language::trans(implode("\n", $content));
            $admin_msg = $msg."\nURL: ".WEB_URL.'index.php?module=borrow-report&status=0';
            if (self::$cfg->noreply_email != '') {
                // หัวข้ออีเมล
                $subject = '['.self::$cfg->web_title.'] '.Language::trans('{LNG_Borrow} & {LNG_Return}');
                // ส่งอีเมลไปยังผู้ทำรายการเสมอ
                $err = \Kotchasan\Email::send($order->name.'<'.$order->username.'>', self::$cfg->noreply_email, $subject, nl2br($msg));
                if ($err->error()) {
                    $ret[] = strip_tags($err->getErrorMessage());
                }
                // อีเมลของผู้ดูแล
                $query = \Kotchasan\Model::createQuery()
                    ->select('username', 'name')
                    ->from('user')
                    ->where(array(
                        array('social', 0),
                        array('active', 1),
                        array('username', '!=', $order->username),
                    ))
                    ->andWhere(array(
                        array('status', 1),
                        array('permission', 'LIKE', '%,can_approve_borrow,%'),
                    ), 'OR')
                    ->cacheOn();
                foreach ($query->execute() as $item) {
                    // ส่งอีเมล
                    $err = \Kotchasan\Email::send($item->name.'<'.$item->username.'>', self::$cfg->noreply_email, $subject, $admin_msg);
                    if ($err->error()) {
                        // คืนค่า error
                        $ret[] = strip_tags($err->getErrorMessage());
                    }
                }
            }
            if (!empty(self::$cfg->line_api_key)) {
                // ส่งไลน์
                $err = \Gcms\Line::send($admin_msg);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
            // คืนค่า
            if (self::$cfg->noreply_email != '' || !empty(self::$cfg->line_api_key)) {
                return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", $ret);
            } else {
                return Language::get('Saved successfully');
            }
        }
        // not found
        return Language::get('Unable to complete the transaction');
    }
}
