<?php
/**
 * @package     blocketdcustom
 *
 * @version     0.0.1
 * @copyright   Copyright (C) 2014 Jean-Baptiste Alleaume. Tous droits réservés.
 * @license     http://alleau.me/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 */

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockEtdCustomModel extends ObjectModel {

	static function getCustoms($hook=false, $published=false, $id_lang= false, $id_shop = false) {

		$db = Db::getInstance();
		$context = Context::getContext();

		$sql = '
			SELECT a.*, b.title, b.content, c.published, c.exceptions, c.ordering
			FROM ' . _DB_PREFIX_ . 'etd_custom AS a
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_lang AS b ON b.id_custom = a.id
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_shop AS c ON c.id_custom = a.id
			WHERE
				' . ($published ? 'c.published = 1 AND' : '') . '
				' . (is_string($hook) ? "a.hook = '" . $db->escape($hook) . "' AND" : "") . '
				b.id_lang = '.($id_lang ? (int)$id_lang : (int)$context->language->id).'
				AND c.id_shop IN('.($id_shop ? (int)$id_shop : implode(', ', Shop::getContextListShopID())).')
			ORDER BY c.ordering ASC
		';

		return $db->executeS($sql);
	}

	static function getCustom($id_custom, $id_shop = false) {

		$db = Db::getInstance();

		$sql = '
			SELECT a.*, b.id_lang, b.title, b.content, c.published, c.exceptions, c.ordering
			FROM ' . _DB_PREFIX_ . 'etd_custom AS a
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_lang AS b ON b.id_custom = a.id
			LEFT JOIN ' . _DB_PREFIX_ . 'etd_custom_shop AS c ON c.id_custom = a.id
			WHERE
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

			$sql = "update " . _DB_PREFIX_ . "etd_custom set hook = '" . $db->escape($custom['hook']) . "', etdhook = '" . $db->escape($custom['etdhook']) . "', css = '" . $db->escape($custom['css']) . "', access = " . (int) $custom['access'] . ", showtitle = " . (int) $custom['showtitle'] . ", params = '" . $db->escape($custom['params']) . "' where id = " . $custom['id'];
			$db->execute($sql);
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom_lang where id_custom = " . $custom['id'];
			$db->execute($sql);
			$sql = "delete from " . _DB_PREFIX_ . "etd_custom_shop where id_custom = " . $custom['id'];
			$db->execute($sql);

		} else {

			$sql = "insert into " . _DB_PREFIX_ . "etd_custom (hook, etdhook, access, showtitle, params, css) VALUES ('" . $db->escape($custom['hook']) . "', '" . $db->escape($custom['etdhook']) . "', " . (int) $custom['access'] . ", " . (int) $custom['showtitle'] . ", '" . $db->escape($custom['params']) . "', '" . $db->escape($custom['css']) . "')";
			$db->execute($sql);

			$custom['id'] = $db->Insert_ID();

		}

		foreach ($custom['title'] as $id_lang => $v) {
			$sql = 'INSERT INTO `'._DB_PREFIX_.'etd_custom_lang` (`id_custom`, `id_lang`, `title`, `content`)
					VALUES('.(int)$custom['id'].', '.(int)$id_lang.', "'.$db->escape($custom['title'][$id_lang]).'", "'.$db->escape($custom['content'][$id_lang], true).'")';
			$db->execute($sql);
		}

		foreach ($custom['shops'] as $id_shop) {
			$sql = 'INSERT INTO `'._DB_PREFIX_.'etd_custom_shop` (`id_custom`, `id_shop`, `published`, `ordering`, `exceptions`)
				VALUES('.(int)$custom['id'].', '.(int)$id_shop.', '.(int) $custom['published'].', '.(int) $custom['ordering'].', "'.$db->escape($custom['exceptions']).'")';
			$db->execute($sql);
		}

        // On vide le cache.
        self::cleanCache();

		return true;

	}

	public static function duplicateCustom($id_custom) {

		$custom              = self::getCustom($id_custom);
		$custom['id']        = '0';
		$custom['published'] = '0';
        $custom['shops']     = Shop::getContextListShopID();

		foreach ($custom['title'] as &$title) {
			$title = self::increment($title);
		}

		return self::storeCustom($custom);

	}

    public  static function cleanCache() {

        // On vide le cache Prestashop.
        $cache = Cache::getInstance();
        $cache->delete("blocketdcustom_*");

        // On vide le cache smarty.
		if (is_callable(array('Tools', 'clearSmartyCache'))) {
			Tools::clearSmartyCache();
		} else {
			Tools::clearCache();
		}

    }

	/**
	 * Increments a trailing number in a string.
	 *
	 * Used to easily create distinct labels when copying objects. The method has the following styles:
	 *
	 * default: "Label" becomes "Label (2)"
	 * dash:    "Label" becomes "Label-2"
	 *
	 * @param   string   $string  The source string.
	 * @param   integer  $n       If supplied, this number is used for the copy, otherwise it is the 'next' number.
	 *
	 * @return  string  The incremented string.
	 *
	 * @since   1.0
	 */
	protected static function increment($string, $n = 0)
	{
		$styleSpec = array(
			array('#\((\d+)\)$#', '#\(\d+\)$#'),
			array(' (%d)', '(%d)'),
		);

		// Regular expression search and replace patterns.
		if (is_array($styleSpec[0]))
		{
			$rxSearch = $styleSpec[0][0];
			$rxReplace = $styleSpec[0][1];
		}
		else
		{
			$rxSearch = $rxReplace = $styleSpec[0];
		}

		// New and old (existing) sprintf formats.
		if (is_array($styleSpec[1]))
		{
			$newFormat = $styleSpec[1][0];
			$oldFormat = $styleSpec[1][1];
		}
		else
		{
			$newFormat = $oldFormat = $styleSpec[1];
		}

		// Check if we are incrementing an existing pattern, or appending a new one.
		if (preg_match($rxSearch, $string, $matches))
		{
			$n = empty($n) ? ($matches[1] + 1) : $n;
			$string = preg_replace($rxReplace, sprintf($oldFormat, $n), $string);
		}
		else
		{
			$n = empty($n) ? 2 : $n;
			$string .= sprintf($newFormat, $n);
		}

		return $string;
	}

}