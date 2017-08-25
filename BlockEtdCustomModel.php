<?php
/**
 * @package     blocketdcustom
 *
 * @version     1.6
 * @copyright   Copyright (C) 2017 ETD Solutions. Tous droits réservés.
 * @license     https://raw.githubusercontent.com/jbanety/blocketdcustom/master/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 */

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockEtdCustomModel extends ObjectModel {

	static function getCustoms($hook=false, $published=false, $id_lang= false, $id_shop = false) {

		$db = Db::getInstance();
		$context = Context::getContext();

		$sql = '
			SELECT a.*, b.title, b.content
			FROM ' . _DB_PREFIX_ . 'etd_custom AS a
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_lang AS b ON b.id_custom = a.id
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_shop AS c ON c.id_custom = a.id
			WHERE
				' . ($published ? 'a.published = 1 AND' : '') . '
				' . (is_string($hook) ? "a.hook = '" . $db->escape($hook) . "' AND" : "") . '
				b.id_lang = '.($id_lang ? (int)$id_lang : (int)$context->language->id).'
				AND c.id_shop IN('.($id_shop ? (int)$id_shop : implode(', ', Shop::getContextListShopID())).')
			ORDER BY a.hook, a.ordering ASC
		';

		return $db->executeS($sql);
	}

	static function getCustom($id_custom, $id_shop = false, $published = false) {

		$db = Db::getInstance();

		$sql = '
			SELECT a.*, b.id_lang, b.title, b.content
			FROM ' . _DB_PREFIX_ . 'etd_custom AS a
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_lang AS b ON b.id_custom = a.id
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_shop AS c ON c.id_custom = a.id
			WHERE
				' . ($published ? 'a.published = 1 AND' : '') . '
				a.id = ' . (int) $id_custom . '
				AND c.id_shop IN('.($id_shop ? (int)$id_shop : implode(', ', Shop::getContextListShopID())).')
		';

		$customs = $db->executeS($sql);

		$custom = $customs[0];
		$custom['params'] = json_decode($custom['params']);
		$custom['title'] = array();
		$custom['content'] = array();

		foreach ($customs as $tmp) {
			$custom['title'][(int)$tmp['id_lang']] = $tmp['title'];
			$custom['content'][(int)$tmp['id_lang']] = $tmp['content'];
		}

		return $custom;
	}

	static function deleteCustom($id_custom) {

		$db = Db::getInstance();
		$id_custom = (int) $id_custom;

		if ($id_custom) {
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom where id = " . $id_custom;
			$db->execute($sql);
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom_lang where id_custom = " . $id_custom;
			$db->execute($sql);
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom_shop where id_custom = " . $id_custom;
			$db->execute($sql);
		}

        // On vide le cache.
        self::cleanCache();

		return true;

	}

	static function storeCustom($custom, $update=false) {

		$db = Db::getInstance();

		if ($update && is_int($custom['id']) && $custom['id'] > 0) {

			$sql = "update " . _DB_PREFIX_ . "etd_custom set hook = '" . $db->escape($custom['hook']) . "', etdhook = '" . $db->escape($custom['etdhook']) . "', block_tag = '" . $db->escape($custom['block_tag']) . "', title_tag = '" . $db->escape($custom['title_tag']) . "', content_tag = '" . $db->escape($custom['content_tag']) . "', block_class = '" . $db->escape($custom['block_class']) . "', title_class = '" . $db->escape($custom['title_class']) . "', content_class = '" . $db->escape($custom['content_class']) . "', access = " . (int) $custom['access'] . ", showtitle = " . (int) $custom['showtitle'] . ", params = '" . $db->escape($custom['params']) . "', exceptions = '" . $db->escape($custom['exceptions']) . "', ordering = '" . (int) $custom['ordering'] . "', published = '" . (int) $custom['published'] . "' where id = " . $custom['id'];
			$db->execute($sql);
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom_lang where id_custom = " . $custom['id'];
			$db->execute($sql);
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom_shop where id_custom = " . $custom['id'];
			$db->execute($sql);

		} else {

            $sql = "SELECT MAX(ordering) AS ordering FROM " . _DB_PREFIX_ . "etd_custom WHERE hook = '" . $db->escape($custom['hook']) . "'";
            $res = $db->query($sql);
            $ordering = $res->fetchColumn();

            if (isset($ordering)) {
                $custom['ordering'] = (int) $ordering + 1;
            } else {
                $custom['ordering'] = 1;
            }

			$sql = "insert into " . _DB_PREFIX_ . "etd_custom (hook, etdhook, access, showtitle, params, block_tag, title_tag, content_tag, block_class, title_class, content_class, ordering, published, exceptions) VALUES ('" . $db->escape($custom['hook']) . "', '" . $db->escape($custom['etdhook']) . "', " . (int) $custom['access'] . ", " . (int) $custom['showtitle'] . ", '" . $db->escape($custom['params']) . "', '" . $db->escape($custom['block_tag']) . "', '" . $db->escape($custom['title_tag']) . "', '" . $db->escape($custom['content_tag']) . "', '" . $db->escape($custom['block_class']) . "', '" . $db->escape($custom['title_class']) . "', '" . $db->escape($custom['content_class']) . "', '" . (int) $custom['ordering'] . "', '" . (int) $custom['published'] . "', '" . $db->escape($custom['exceptions']) . "')";
			$db->execute($sql);

			$custom['id'] = $db->Insert_ID();

		}

		foreach ($custom['title'] as $id_lang => $v) {
			$sql = 'INSERT INTO `'._DB_PREFIX_.'etd_custom_lang` (`id_custom`, `id_lang`, `title`, `content`)
					VALUES('.(int)$custom['id'].', '.(int)$id_lang.', "'.$db->escape($custom['title'][$id_lang]).'", "'.$db->escape($custom['content'][$id_lang], true).'")';
			$db->execute($sql);
		}

		foreach ($custom['shops'] as $id_shop) {
			$sql = 'INSERT INTO `'._DB_PREFIX_.'etd_custom_shop` (`id_custom`, `id_shop`)
				VALUES('.(int)$custom['id'].', '.(int)$id_shop.')';
			$db->execute($sql);
		}

        // On vide le cache.
        self::cleanCache();

		return true;

	}

    static public function moveCustom($id_custom, $delta) {

        // If the change is none, do nothing.
        if (empty($delta)) {
            return true;
        }

        $db     = Db::getInstance();
        $row    = null;
        $wheres = [];
        $order  = "";

        $custom = self::getCustom($id_custom);

        // Select the primary key and ordering values from the table.
        $query = 'SELECT `id`, `ordering` FROM `'._DB_PREFIX_.'etd_custom`';

        // If the movement delta is negative move the row up.
        if ($delta < 0) {

            $wheres[] = "`ordering` < " . (int) $custom['ordering'];
            $order    = "`ordering` DESC";

        } elseif ($delta > 0) { // If the movement delta is positive move the row down.

            $wheres[] = "`ordering` > " . (int) $custom['ordering'];
            $order    = "`ordering` ASC";

        }

        // Add the custom WHERE hook clause if set.
        $wheres[] = "`hook` = '" . $db->escape($custom['hook']) . "'";

        $query .= " WHERE " . implode(" AND ", $wheres) . " ORDER BY " . $order;

        // Select the first row with the criteria.
        $row = $db->executeS($query);

        // If a row is found, move the item.
        if (!empty($row)) {

            $row = $row[0];

            // Update the ordering field for this instance to the row's ordering value.
            $query = 'UPDATE `'._DB_PREFIX_.'etd_custom` SET `ordering` = ' . (int) $row['ordering'] . ' WHERE `id` = ' . (int) $id_custom;
            $db->execute($query);

            // Update dthe ordering field for the row to this instance's ordering value.
            $query = 'UPDATE `'._DB_PREFIX_.'etd_custom` SET `ordering` = ' . (int) $custom['ordering'] . ' WHERE `id` = ' . (int) $row['id'];
            $db->execute($query);

        }

        return true;
    }

    public static function cleanCache() {

        // On vide le cache Prestashop.
        $cache = Cache::getInstance();
        $cache->delete("blocketdcustom_*");

        // On vide le cache smarty.
        Tools::clearSmartyCache();

    }

}