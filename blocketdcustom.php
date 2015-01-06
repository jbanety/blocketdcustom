<?php
/**
 * @package     blocketdcustom
 *
 * @version     1.0
 * @copyright   Copyright (C) 2014 Jean-Baptiste Alleaume. Tous droits réservés.
 * @license     http://alleau.me/LICENSE
 * @author      Jean-Baptiste Alleaume http://alleau.me
 */

if (!defined('_CAN_LOAD_FILES_'))
	exit;

include_once(dirname(__FILE__) . '/BlockEtdCustomModel.php');

class BlockEtdCustom extends Module {

	private $_html;
	private $_display;

	/**
	 * Hooks disponibles dans Prestashop.
	 * @var array
	 */
	protected $hooks = array();

	public function __construct() {

		$this->name = 'blocketdcustom';
		$this->tab = 'front_office_features';
		$this->version = '1.0';
		$this->author = 'ETD Solutions';

		$this->bootstrap = true;
		parent::__construct();

		$this->displayName = $this->l('ETD Custom Content');
		$this->description = $this->l('Add custom block on multiple hooks.');
		$this->secure_key = Tools::encrypt($this->name);
		$this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);

		// On détecte les hooks disponibles.
		$this->populateHooks();
	}

	/**
	 * Méthode magique pour appeler pouvoir appeler n'importe quel hook sur ce module.
	 *
	 * @param $method string Nom de la méthode appellée
	 * @param $args   array Paramètres passées à la méthode
	 *
	 * @return mixed  Le résultat de la méthode ou false en cas d'erreur
	 */
	public function __call($method, $args) {

		// Si la méthode existe, on l'appel.
		if (function_exists($method)) {
			return call_user_func_array($method, $args);
		} elseif ($this->isRegisteredHook($method)) { // C'est un hook détecté !
			return $this->executeHook($method, $args);
		}

		return false;
	}

	public function install() {

		return
			parent::install() &&
			$this->installDB() &&
			$this->_clearCache('blocketdcustom.tpl');

	}

	protected function installDB() {
		$db = Db::getInstance();
		$sql = file_get_contents(dirname(__FILE__).'/sql/install.sql');
		if ($sql === false)
			return false;
		$sql = str_replace('#__', _DB_PREFIX_, $sql);
		return $db->execute($sql);
	}

	public function uninstall() {
		return
			parent::uninstall() &&
			$this->uninstallDB();
	}

	protected function uninstallDB() {
		$db = Db::getInstance();
		$sql = file_get_contents(dirname(__FILE__).'/sql/uninstall.sql');
		if ($sql === false)
			return false;
		$sql = str_replace('#__', _DB_PREFIX_, $sql);
		return $db->execute($sql);
	}

	public function initToolbar() {

		$current_index = AdminController::$currentIndex;
		$token = Tools::getAdminTokenLite('AdminModules');

		$back = Tools::safeOutput(Tools::getValue('back', ''));

		if (!isset($back) || empty($back))
			$back = $current_index.'&amp;configure='.$this->name.'&token='.$token;

		switch ($this->_display) {
			case 'add':
				$this->toolbar_btn['cancel'] = array(
					'href' => $back,
					'desc' => $this->l('Cancel')
				);
				break;
			case 'edit':
				$this->toolbar_btn['cancel'] = array(
					'href' => $back,
					'desc' => $this->l('Cancel')
				);
				break;
			case 'index':
				$this->toolbar_btn['new'] = array(
					'href' => $current_index.'&amp;configure='.$this->name.'&amp;token='.$token.'&amp;addCustom',
					'desc' => $this->l('Add new'),
				);
                $this->toolbar_btn['refresh-cache'] = array(
                    'href' => $current_index.'&amp;configure='.$this->name.'&amp;token='.$token.'&amp;clearCache',
                    'desc' => $this->l('Clear cache'),
					'class' => 'icon-refresh'
                );
				break;
			default:
				break;
		}

		return $this->toolbar_btn;
	}

	protected function displayForm() {

		$this->context->controller->addJqueryPlugin('tablednd');
		$this->context->controller->addJS(_PS_JS_DIR_.'admin-dnd.js');

		$current_index = AdminController::$currentIndex;
		$token = Tools::getAdminTokenLite('AdminModules');

		$this->_display = 'index';

		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Custom blocks'),
				'icon' => 'icon-list-alt'
			),
			'input' => array(
				array(
					'type' => 'customs',
					'label' => $this->l('Custom blocks:'),
					'name' => 'customs',
					'values' => BlockEtdCustomModel::getCustoms(),
					'desc' => $this->l(''),
				)
			),
			'buttons' => array(
				'newCustom' => array(
					'title' => $this->l('Add new'),
					'href' => $current_index.'&amp;configure='.$this->name.'&amp;token='.$token.'&amp;addCustom',
					'class' => 'pull-right',
					'icon' => 'process-icon-new'
				)
			)
		);

		$helper = $this->initForm();
		$helper->submit_action = '';
		$helper->title = $this->l('Custom blocks');

		if (isset($this->fields_value))
			$helper->fields_value = $this->fields_value;
		$this->_html .= $helper->generateForm($this->fields_form);

		return;
	}

	protected function displayAddForm() {

		$this->_display = 'add';
		$custom = null;

		if (Tools::isSubmit('editCustom') && Tools::getValue('id_custom')) {
			$this->_display = 'edit';
			$id_custom = (int)Tools::getValue('id_custom');
			$custom = BlockEtdCustomModel::getCustom($id_custom);
		}

		// On récupère les controllers sur lesquels faire les exceptions.
		$controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
		ksort($controllers);

		$this->fields_form[0]['form'] = array(
			'legend' => array(
				'title' => $this->l('Details'),
				'icon' => isset($custom) ? 'icon-edit' : 'icon-plus-square'
			),
			'input' => array(
				array(
					'type' => 'hidden',
					'name' => 'id_custom'
				),
				array(
					'type' => 'hidden',
					'name' => 'old_hook'
				),
				array(
					'type' => 'hooks',
					'label' => $this->l('Hook:'),
					'name' => 'hook',
					'hooks' => $this->hooks,
					'required' => true,
					'desc' => $this->l('')
				),
				array(
					'type' => 'text',
					'label' => $this->l('ETD Hook:'),
					'name' => 'etdhook',
					'desc' => $this->l('Hook used by templates created by ETD Solutions'),
					'size' => 40,
					'maxlength' => 50,
				),
				array(
					'type' => 'text',
					'label' => $this->l('Title:'),
					'name' => 'title',
					'lang' => true,
					'desc' => $this->l(''),
					'size' => 40,
					'maxlength' => 255,
					'required' => true
				),
				array(
					'type' => 'textarea',
					'label' => $this->l('Content:'),
					'name' => 'content',
					'lang' => true,
					'desc' => $this->l(''),
					//'autoload_rte' => true,
					'rows' => 10,
					'cols' => 40,
					'required' => true
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Published:'),
					'name' => 'published',
					'desc' => $this->l(''),
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'published_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'published_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					)
				),
				array(
					'type' => 'switch',
					'label' => $this->l('Show title:'),
					'name' => 'showtitle',
					'desc' => $this->l(''),
					'is_bool' => true,
					'values' => array(
						array(
							'id' => 'showtitle_on',
							'value' => 1,
							'label' => $this->l('Yes')
						),
						array(
							'id' => 'showtitle_off',
							'value' => 0,
							'label' => $this->l('No')
						)
					)
				),
				array(
					'type' => 'select',
					'label' => $this->l('Access:'),
					'name' => 'access',
					'default_value' => 0,
					'options' => array(
						'id' => 'id',
						'name' => 'name',
						'query' => array(
							array(
								'id' => 0,
								'name' => $this->l('Public')
							),
							array(
								'id' => 1,
								'name' => $this->l('Guests')
							),
							array(
								'id' => 2,
								'name' => $this->l('Customers')
							)
						)
					),
					'desc' => $this->l('')
				),
				array(
					'type' => 'text',
					'label' => $this->l('CSS Class:'),
					'name' => 'css',
					'desc' => $this->l('Custom CSS Class'),
					'size' => 40,
					'maxlength' => 255,
				),
				array(
					'type' => 'exceptions',
					'label' => $this->l('Exceptions:'),
					'name' => 'exceptions',
					'controllers' => $controllers,
					'desc' => $this->l('')
				)
			),
			'submit' => array(
				'name' => 'submitCustom',
				'title' => $this->l('Save'),
			)
		);

		$this->context->controller->getLanguages();

		$this->fields_value['old_hook'] = '';
		$this->fields_value['id_custom'] = 0;

		if (Tools::getValue('hook'))
			$this->fields_value['hook'] = Tools::getValue('hook');

		else if (isset($custom))
			$this->fields_value['hook'] = $custom['hook'];
		else
			$this->fields_value['hook'] = '';

		$this->fields_value['hook_custom'] = $this->fields_value['hook'];

		if (Tools::getValue('etdhook'))
			$this->fields_value['etdhook'] = Tools::getValue('etdhook');
		else if (isset($custom))
			$this->fields_value['etdhook'] = $custom['etdhook'];
		else
			$this->fields_value['etdhook'] = '';

		foreach ($this->context->controller->_languages as $language) {
			if (Tools::getValue('title_'.$language['id_lang']))
				$this->fields_value['title'][$language['id_lang']] = Tools::getValue('title_'.$language['id_lang']);
			else if (isset($custom) && isset($custom['title'][$language['id_lang']]))
				$this->fields_value['title'][$language['id_lang']] = $custom['title'][$language['id_lang']];
			else
				$this->fields_value['title'][$language['id_lang']] = '';
		}

		foreach ($this->context->controller->_languages as $language) {
			if (Tools::getValue('content_'.$language['id_lang']))
				$this->fields_value['content'][$language['id_lang']] = Tools::getValue('content_'.$language['id_lang']);
			else if (isset($custom) && isset($custom['content'][$language['id_lang']]))
				$this->fields_value['content'][$language['id_lang']] = $custom['content'][$language['id_lang']];
			else
				$this->fields_value['content'][$language['id_lang']] = '';
		}

		if (Tools::getValue('published'))
			$this->fields_value['published'] = Tools::getValue('published');
		else if (isset($custom))
			$this->fields_value['published'] = $custom['published'];
		else
			$this->fields_value['published'] = 0;

		if (Tools::getValue('access'))
			$this->fields_value['access'] = Tools::getValue('access');
		else if (isset($custom))
			$this->fields_value['access'] = $custom['access'];
		else
			$this->fields_value['access'] = 0;

		if (Tools::getValue('showtitle'))
			$this->fields_value['showtitle'] = Tools::getValue('showtitle');
		else if (isset($custom))
			$this->fields_value['showtitle'] = $custom['showtitle'];
		else
			$this->fields_value['showtitle'] = 0;

		if (Tools::getValue('css'))
			$this->fields_value['css'] = Tools::getValue('css');
		else if (isset($custom))
			$this->fields_value['css'] = $custom['css'];
		else
			$this->fields_value['css'] = '';

		if (Tools::getValue('exceptions'))
			$this->fields_value['exceptions'] = explode(',',Tools::getValue('exceptions'));
		else if (isset($custom))
			$this->fields_value['exceptions'] = explode(',',$custom['exceptions']);
		else
			$this->fields_value['exceptions'] = array();

		$helper = $this->initForm();
		$helper->submit_action = '';
		$helper->title = ($this->_display == 'add') ? $this->l('Add new custom block') : $this->l('Edit custom block');

		if (isset($id_custom)) {
			$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name.'&id_custom='.$id_custom;
			$helper->submit_action = 'editCustom';
			$this->fields_value['id_custom'] = $id_custom;
			$this->fields_value['old_hook'] = $custom['hook'];
		}
		else
			$helper->submit_action = 'addCustom';

		$helper->fields_value = $this->fields_value;
		$this->_html .= $helper->generateForm($this->fields_form);

		return;
	}

	private function initForm() {

		$helper = new HelperForm();

		$helper->module = $this;
		$helper->name_controller = 'blocketdcustom';
		$helper->identifier = $this->identifier;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->languages = $this->context->controller->_languages;
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;
		$helper->default_form_language = $this->context->controller->default_form_language;
		$helper->allow_employee_form_lang = $this->context->controller->allow_employee_form_lang;
		$helper->toolbar_scroll = true;
		$helper->toolbar_btn = $this->initToolbar();

		return $helper;
	}

	protected function _postValidation() {

		$this->_errors = array();

		if (Tools::isSubmit('submitLink')) {

			if (!Tools::getValue('hook')) {
				$this->_errors[] = $this->l('Please choose a valid hook.');
			}

			if (Tools::getValue('hook') == "custom" && !Tools::getValue('hook_custom')) {
				$this->_errors[] = $this->l('Please choose a valid hook.');
			}

			$languages = LanguageCore::getLanguages(true);
			foreach ($languages as $language) {

				if (!Tools::getValue('title_'.$language['id_lang']))
					$this->_errors[] = $this->l('You must type a title.');

				if (!Tools::getValue('content_'.$language['id_lang']))
					$this->_errors[] = $this->l('You must type a content.');
			}

			$exceptions = Tools::getValue('exceptions');
			$exceptions = explode(',', str_replace(' ', '', $exceptions));
			foreach ($exceptions as $key => $except) {
				if (empty($except))
					unset($exceptions[$key]);
				else if (!Validate::isFileName($except))
					$this->errors[] = Tools::displayError('No valid value for field exceptions has been defined.');
			}

		}

		if (count($this->_errors)) {
			foreach ($this->_errors as $err)
				$this->_html .= '<div class="alert error">'.$err.'</div>';

			return false;
		}
		return true;
	}

	private function _postProcess() {

		if ($this->_postValidation() == false)
			return false;

		$this->_errors = array();
		if (Tools::isSubmit('submitCustom')) {

			$title = array();
			$content = array();
			foreach ($this->context->controller->getLanguages() as $lang) {
				$title[$lang['id_lang']] = Tools::getValue('title_'.$lang['id_lang']);
				$content[$lang['id_lang']] = Tools::getValue('content_'.$lang['id_lang']);
			}

			$custom = array();
			$custom['title'] = $title;
			$custom['content'] = $content;
			$custom['shops'] = Shop::getContextListShopID();
			$custom['published'] = Tools::getValue('published', 0);
			$custom['showtitle'] = Tools::getValue('showtitle', 0);
			$custom['access'] = Tools::getValue('access', 0);
			$custom['hook'] = Tools::getValue('hook', '');
			$custom['etdhook'] = Tools::getValue('etdhook', '');
			$custom['css'] = Tools::getValue('css', '');
			$custom['exceptions'] = Tools::getValue('exceptions', '');

			if (Tools::getValue('hook') == "custom") {
				$custom['hook'] = Tools::getValue('hook_custom', '');
			}

			if (Tools::isSubmit('addCustom')) {

				if (!BlockEtdCustomModel::storeCustom($custom))
					return false;

				if (!$this->registerCustomHook($custom))
					return false;

				$redirect = 'addCustomConfirmation';

			} else if (Tools::isSubmit('editCustom')) {

				$custom['id'] = (int) Tools::getValue('id_custom');
				BlockEtdCustomModel::storeCustom($custom, true);
				$redirect = 'editCustomConfirmation';

			}

			Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&'.$redirect);

		} elseif (Tools::isSubmit('deleteCustom') && Tools::getValue('id_custom')) {
			$id_custom = (int) Tools::getvalue('id_custom');

			if ($id_custom) {
				if (!BlockEtdCustomModel::deleteCustom($id_custom))
					return false;

				if (!$this->unregisterCustomHook($id_custom))
					return false;

				Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&deleteCustomConfirmation');
			} else
				$this->_html .= $this->displayError($this->l('You are trying to delete a non-existing link. '));

		} elseif (Tools::isSubmit('orderUp') && Tools::getValue('id_custom')) {
			$id_custom = (int) Tools::getvalue('id_custom');

			if ($id_custom) {
				if (!BlockEtdCustomModel::orderUp($id_custom)) {
					$this->_html .= $this->displayError($this->l('An error occured when trying order a link. '));
				}
			} else
				$this->_html .= $this->displayError($this->l('You are trying to order a non-existing link. '));

		} elseif (Tools::isSubmit('orderDown') && Tools::getValue('id_custom')) {
			$id_custom = (int) Tools::getvalue('id_custom');

			if ($id_custom) {
				if (!BlockEtdCustomModel::orderDown($id_custom)) {
					$this->_html .= $this->displayError($this->l('An error occured when trying order a link. '));
				}
			} else
				$this->_html .= $this->displayError($this->l('You are trying to order a non-existing link. '));

        } elseif (Tools::isSubmit('clearCache')) {

                BlockEtdCustomModel::cleanCache();
                Tools::redirectAdmin(AdminController::$currentIndex.'&configure='.$this->name.'&token='.Tools::getAdminTokenLite('AdminModules').'&clearCacheConfirmation');

        } elseif (Tools::isSubmit('addCustomConfirmation'))
			$this->_html .= $this->displayConfirmation($this->l('Custom block added.'));
		elseif (Tools::isSubmit('editCustomConfirmation'))
			$this->_html .= $this->displayConfirmation($this->l('Custom block edited.'));
        elseif (Tools::isSubmit('clearCacheConfirmation'))
            $this->_html .= $this->displayConfirmation($this->l('Cache cleared.'));

	}

	public function getContent() {

		$this->_html = '';
		$this->_postProcess();

		if (Tools::isSubmit('addCustom') || Tools::isSubmit('editCustom'))
			$this->displayAddForm();
		else
			$this->displayForm();

		$this->_html .= "
	<script>
	jQuery(document).ready(function() {
		$('#desc--save').on('click', function(e) {
			e.preventDefault();
			$('form.blocketdcustom').submit();
		});
	});
	</script>";

		return $this->_html;
	}

	protected function registerCustomHook($custom) {

		$id_hook = (int) Hook::getIdByName($custom['hook']);
		$hook = new Hook($id_hook);

		if (!$id_hook || !Validate::isLoadedObject($hook)) {
			$this->errors[] = $this->displayError('Hook cannot be loaded.');
		}

		if (Hook::getModulesFromHook($id_hook, $this->id)) {
			$this->errors[] = $this->displayError('This module has already been transplanted to this hook');
		}

		if (!$this->isHookableOn($hook->name)) {
			$this->errors[] = $this->displayError('This module cannot be transplanted to this hook.');
		}

		if (!$this->registerHook($hook->name, $custom['shops'])) {
			$this->errors[] = $this->displayError('An error occurred while transplanting the module to its hook.');
		}

		if (count($this->_errors)) {
			foreach ($this->_errors as $err)
				$this->_html .= '<div class="alert error">'.$err.'</div>';

			return false;
		}

		return true;

	}

	protected function unregisterCustomHook($custom) {

		/*if (is_int($custom)) {
			$custom = BlockEtdCustomModel::getCustom($custom);
		}

		$id_hook = (int) Hook::getIdByName($custom['hook']);
		$hook = new Hook($id_hook);

		if (!$id_hook || !Validate::isLoadedObject($hook)) {
			$this->errors[] = $this->displayError('Hook cannot be loaded.');
		}

		if (!$this->unregisterHook($hook->name, $custom['shops'])) {
			$this->errors[] = $this->displayError('An error occurred while deleting the module from its hook.');
		}

		if (count($this->_errors)) {
			foreach ($this->_errors as $err)
				$this->_html .= '<div class="alert error">'.$err.'</div>';

			return false;
		}*/

		return true;

	}

	/**
	 * Méthode générique appelée lors des hooks.
	 *
	 * @param $method
	 * @param $params
	 *
	 * @return bool
	 */
	public function executeHook($method, $params) {

		// Résultat par défaut.
		$result = false;

		// On récupère le nom du hook.
		$hook = str_replace('hook', '', $method);

		// On récupère les blocs attachés à ce hook et publiés.
		$customs = BlockEtdCustomModel::getCustoms($hook, true);

		if (count($customs)) {

			$result = '';

			foreach ($customs as $custom) {
				$result .= $this->fetchContent($custom);
			}

		}

		return $result;

	}

	protected function fetchContent($custom) {

		// On récupère le nom du controller.
		$controller = Dispatcher::getInstance()->getController();

		// Si le controller est exclut, on renvoi une chaine vide
		if (array_key_exists('exception', $custom) && strpos($custom['exception'], $controller) !== false) {
			return '';
		}

		$cacheId = $this->getCacheId($this->name . '|' . $custom['id']);
		if (!$this->isCached('blocketdcustom.tpl', $cacheId)) {
			$this->smarty->assign(array(
				'content' => $custom['content'],
				'title' => $custom['title'],
				'showtitle' => $custom['showtitle'],
				'hook' => $custom['hook'],
				'etdhook' => $custom['etdhook'],
			 	'css' => $custom['css']
  			));
		}
		return $this->display(__FILE__, 'blocketdcustom.tpl', $cacheId);

	}

	/**
	 * Méthode pour détecter tous les hooks d'affichage disponibles.
	 */
	protected function populateHooks() {

		$db = Db::getInstance(_PS_USE_SQL_SLAVE_);
		$sql = 'SELECT name FROM `'._DB_PREFIX_.'hook` ORDER BY `name`';
		$hooks = $db->executeS($sql);

		if (count($hooks)) {
			foreach( $hooks as $hook ) {
				if ( strpos($hook['name'],'display') === 0 )
					$this->hooks[] = $hook['name'];
			}
		}

	}

	protected function isRegisteredHook($method) {

		// On récupère le nom du hook.
		$method = str_replace('hook', '', $method);

		// On contrôle si le hook est disponible
		return in_array( $method , $this->hooks );

	}

}