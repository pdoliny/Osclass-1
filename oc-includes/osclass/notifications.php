<?php
/*
 *      OSCLass – software for creating and publishing online classified
 *                           advertising platforms
 *
 *                        Copyright (C) 2010 OSCLASS
 *
 *       This program is free software: you can redistribute it and/or
 *     modify it under the terms of the GNU Affero General Public License
 *     as published by the Free Software Foundation, either version 3 of
 *            the License, or (at your option) any later version.
 *
 *     This program is distributed in the hope that it will be useful, but
 *         WITHOUT ANY WARRANTY; without even the implied warranty of
 *        MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU Affero General Public License for more details.
 *
 *      You should have received a copy of the GNU Affero General Public
 * License along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

    function alert_new_item($preferences, $item) {
        if (isset($preferences['notify_new_item']) && $preferences['notify_new_item']) {
            require_once LIB_PATH . 'phpmailer/class.phpmailer.php';
            $mail = new PHPMailer;
            $mail->CharSet = "utf-8";
            $mail->Host = 'localhost';
            $mail->From = ( isset($preferences['contactEmail']) ) ? $preferences['contactEmail'] : 'no-reply@osclass.org';
            $mail->FromName = ( isset($preferences['pageTitle']) ) ? $preferences['pageTitle'] : __('OSClass application');
            $mail->Subject = '[ ' . __('New item') . ' ] ' . $preferences['pageTitle'];
            $mail->AddAddress($preferences['contactEmail'], $preferences['pageTitle']);
            $mail->IsHTML(true);
            $body = '';
            $body .= __('Contact Name') . ': ' . $item['s_contact_name'] . '<br/>';
            $body .= __('Contact E-mail') . ': ' . $item['s_contact_email'] . '<br/>';
            if (isset($item['locale'])) {
                foreach ($item['locale'] as $locale => $data) {
                    $locale_name = Locale::newInstance()->listWhere("pk_c_code = '" . $locale . "'");
                    $body .= '<br/>';
                    if (isset($locale_name[0]) && isset($locale_name[0]['s_name'])) {
                        $body .= __('Language') . ': ' . $locale_name[0]['s_name'] . '<br/>';
                    } else {
                        $body .= __('Language') . ': ' . $locale . '<br/>';
                    }
                    $body .= __('Title') . ': ' . $data['s_title'] . '<br/>';
                    $body .= __('Description') . ': ' . $data['s_description'] . '<br/>';
                    $body .= '<br/>';
                }
            } else {
                $body .= __('Title') . ': ' . $item['s_title'] . '<br/>';
                $body .= __('Description') . ': ' . $item['s_description'] . '<br/>';
            }
            $body .= __('Price') . ': ' . $item['f_price'] . ' ' . $item['fk_c_currency_code'] . '<br/>';
            $body .= __('Country') . ': ' . $item['s_country'] . '<br/>';
            $body .= __('Region') . ': ' . $item['s_region'] . '<br/>';
            $body .= __('City') . ': ' . $item['s_city'] . '<br/>';
            $body .= __('Url') . ': ' . osc_createItemURL($item, true) . '<br/>';
            $mail->Body = $body;
            if (!$mail->Send())
                echo $mail->ErrorInfo;
        }
    }

    function mail_validation($preferences, $item) {
        if (isset($preferences['enabled_item_validation']) && $preferences['enabled_item_validation']) {
            $from = ( isset($preferences['contactEmail']) ) ? $preferences['contactEmail'] : 'no-reply@osclass.org';
            $from_name = ( isset($preferences['pageTitle']) ) ? $preferences['pageTitle'] : __('OSClass application');
            $subject = __('Validate your ad') . ' - ' . $preferences['pageTitle'];
            $body = '';
            if (isset($preferences['pageTitle'])) {
                $site = $preferences['pageTitle'];
            } else {
                $site = __('OSClass application');
            }
            $body .= __('Dear ') . $item['s_contact_name'] . ',<br/>';
            $body .= __('You\'re receiving this email because an Ad is being placed at ' . $site . '. You are requested to validate this item with the link at the end of the email. If you didn\'t place this ad, please ignore this email. Details of the ads:') . '<br/>';
            $body .= __('Contact Name') . ': ' . $item['s_contact_name'] . '<br/>';
            $body .= __('Contact E-mail') . ': ' . $item['s_contact_email'] . '<br/>';

            if (isset($item['locale'])) {
                foreach ($item['locale'] as $locale => $data) {
                    $locale_name = Locale::newInstance()->listWhere("pk_c_code = '" . $locale . "'");
                    $body .= '<br/>';
                    if (isset($locale_name[0]) && isset($locale_name[0]['s_name'])) {
                        $body .= __('Language') . ': ' . $locale_name[0]['s_name'] . '<br/>';
                    } else {
                        $body .= __('Language') . ': ' . $locale . '<br/>';
                    }
                    $body .= __('Title') . ': ' . $data['s_title'] . '<br/>';
                    $body .= __('Description') . ': ' . $data['s_description'] . '<br/>';
                    $body .= '<br/>';
                }
            } else {
                $body .= __('Title') . ': ' . $item['s_title'] . '<br/>';
                $body .= __('Description') . ': ' . $item['s_description'] . '<br/>';
            }


            $body .= __('Price') . ': ' . $item['f_price'] . ' ' . $item['fk_c_currency_code'] . '<br/>';
            $body .= __('Country') . ': ' . $item['s_country'] . '<br/>';
            $body .= __('Region') . ': ' . $item['s_region'] . '<br/>';
            $body .= __('City') . ': ' . $item['s_city'] . '<br/>';
            $body .= __('Url') . ': ' . osc_createItemURL($item, true) . '<br/>';
            $body .= __('You can validate your ad in this url') . ': <a href="' . ABS_WEB_URL . 'item.php?action=activate&id=' . $item['pk_i_id'] . '&secret=' . $item['s_secret'] . '" >' . ABS_WEB_URL . 'item.php?action=activate&id=' . $item['pk_i_id'] . '&secret=' . $item['s_secret'] . '</a><br/>';
            $body .= "<br/>--<br/>" . $preferences['pageTitle'];

            $params = array(
                'from' => $from,
                'from_name' => $from_name,
                'subject' => $subject,
                'to' => $item['s_contact_email'],
                'to_name' => $item['s_contact_name'],
                'body' => $body,
                'alt_body' => $body
            );
            osc_sendMail($params);
        }
    }
?>
