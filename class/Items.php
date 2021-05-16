<?php

declare(strict_types=1);


namespace XoopsModules\Wgdiaries;

/*
 You may not change or alter any portion of this comment or credits
 of supporting developers from this source code or any supporting source code
 which is considered copyrighted (c) material of the original comment or credit authors.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*/

/**
 * wgDiaries module for xoops
 *
 * @copyright      2020 XOOPS Project (https://xooops.org)
 * @license        GPL 2.0 or later
 * @package        wgdiaries
 * @since          1.0
 * @min_xoops      2.5.9
 * @author         wedega - Email:<webmaster@wedega.com> - Website:<https://xoops.wedega.com>
 */

use XoopsModules\Wgdiaries;

\defined('XOOPS_ROOT_PATH') || die('Restricted access');

/**
 * Class Object Items
 */
class Items extends \XoopsObject
{
	/**
	 * Constructor
	 *
	 * @param null
	 */
	public function __construct()
	{
		$this->initVar('item_id', XOBJ_DTYPE_INT);
		$this->initVar('item_remarks', XOBJ_DTYPE_OTHER);
		$this->initVar('item_datefrom', XOBJ_DTYPE_INT);
		$this->initVar('item_dateto', XOBJ_DTYPE_INT);
		$this->initVar('item_datecreated', XOBJ_DTYPE_INT);
		$this->initVar('item_submitter', XOBJ_DTYPE_INT);
	}

	/**
	 * @static function &getInstance
	 *
	 * @param null
	 */
	public static function getInstance()
	{
		static $instance = false;
		if (!$instance) {
			$instance = new self();
		}
	}

	/**
	 * The new inserted $Id
	 * @return inserted id
	 */
	public function getNewInsertedIdItems()
	{
		$newInsertedId = $GLOBALS['xoopsDB']->getInsertId();
		return $newInsertedId;
	}

	/**
	 * @public function getForm
	 * @param bool $action
	 * @return \XoopsThemeForm
	 */
	public function getFormItems($action = false)
	{
		$helper = \XoopsModules\Wgdiaries\Helper::getInstance();
		if (!$action) {
			$action = $_SERVER['REQUEST_URI'];
		}
		$isAdmin = $GLOBALS['xoopsUser']->isAdmin($GLOBALS['xoopsModule']->mid());
		// Permissions for uploader
		$grouppermHandler = \xoops_getHandler('groupperm');
		$groups = \is_object($GLOBALS['xoopsUser']) ? $GLOBALS['xoopsUser']->getGroups() : XOOPS_GROUP_ANONYMOUS;
		$permissionUpload = $grouppermHandler->checkRight('upload_groups', 32, $groups, $GLOBALS['xoopsModule']->getVar('mid')) ? true : false;
		// Title
		$title = $this->isNew() ? \sprintf(_AM_WGDIARIES_ITEM_ADD) : \sprintf(_AM_WGDIARIES_ITEM_EDIT);
		// Get Theme Form
		\xoops_load('XoopsFormLoader');
		$form = new \XoopsThemeForm($title, 'form', $action, 'post', true);
		$form->setExtra('enctype="multipart/form-data"');
		// Form Editor DhtmlTextArea itemRemarks
		$editorConfigs = [];
		if ($isAdmin) {
			$editor = $helper->getConfig('editor_admin');
		} else {
			$editor = $helper->getConfig('editor_user');
		}
		$editorConfigs['name'] = 'item_remarks';
		$editorConfigs['value'] = $this->getVar('item_remarks', 'e');
		$editorConfigs['rows'] = 5;
		$editorConfigs['cols'] = 40;
		$editorConfigs['width'] = '100%';
		$editorConfigs['height'] = '400px';
		$editorConfigs['editor'] = $editor;
		$form->addElement(new \XoopsFormEditor(_AM_WGDIARIES_ITEM_REMARKS, 'item_remarks', $editorConfigs));
		// Form Text Date Select itemDatefrom
		$itemDatefrom = $this->isNew() ? time() : $this->getVar('item_datefrom');
		$form->addElement(new \XoopsFormDateTime(_AM_WGDIARIES_ITEM_DATEFROM, 'item_datefrom', '', $itemDatefrom), true);
		// Form Text Date Select itemDateto
		$itemDateto = $this->isNew() ? time() : $this->getVar('item_dateto');
		$form->addElement(new \XoopsFormDateTime(_AM_WGDIARIES_ITEM_DATETO, 'item_dateto', '', $itemDateto), true);
		// Form Text Date Select itemDatecreated
		$itemDatecreated = $this->isNew() ? 0 : $this->getVar('item_datecreated');
		$form->addElement(new \XoopsFormTextDateSelect(_AM_WGDIARIES_ITEM_DATECREATED, 'item_datecreated', '', $itemDatecreated));
		// Form Select User itemSubmitter
		$form->addElement(new \XoopsFormSelectUser(_AM_WGDIARIES_ITEM_SUBMITTER, 'item_submitter', false, $this->getVar('item_submitter')));
		// Permissions
		$memberHandler = \xoops_getHandler('member');
		$groupList = $memberHandler->getGroupList();
		$grouppermHandler = \xoops_getHandler('groupperm');
		$fullList[] = \array_keys($groupList);
		if (!$this->isNew()) {
			$groupsIdsApprove = $grouppermHandler->getGroupIds('wgdiaries_approve_items', $this->getVar('item_id'), $GLOBALS['xoopsModule']->getVar('mid'));
			$groupsIdsApprove[] = \array_values($groupsIdsApprove);
			$groupsCanApproveCheckbox = new \XoopsFormCheckBox(_AM_WGDIARIES_PERMISSIONS_APPROVE, 'groups_approve_items[]', $groupsIdsApprove);
			$groupsIdsSubmit = $grouppermHandler->getGroupIds('wgdiaries_submit_items', $this->getVar('item_id'), $GLOBALS['xoopsModule']->getVar('mid'));
			$groupsIdsSubmit[] = \array_values($groupsIdsSubmit);
			$groupsCanSubmitCheckbox = new \XoopsFormCheckBox(_AM_WGDIARIES_PERMISSIONS_SUBMIT, 'groups_submit_items[]', $groupsIdsSubmit);
			$groupsIdsView = $grouppermHandler->getGroupIds('wgdiaries_view_items', $this->getVar('item_id'), $GLOBALS['xoopsModule']->getVar('mid'));
			$groupsIdsView[] = \array_values($groupsIdsView);
			$groupsCanViewCheckbox = new \XoopsFormCheckBox(_AM_WGDIARIES_PERMISSIONS_VIEW, 'groups_view_items[]', $groupsIdsView);
		} else {
			$groupsCanApproveCheckbox = new \XoopsFormCheckBox(_AM_WGDIARIES_PERMISSIONS_APPROVE, 'groups_approve_items[]', $fullList);
			$groupsCanSubmitCheckbox = new \XoopsFormCheckBox(_AM_WGDIARIES_PERMISSIONS_SUBMIT, 'groups_submit_items[]', $fullList);
			$groupsCanViewCheckbox = new \XoopsFormCheckBox(_AM_WGDIARIES_PERMISSIONS_VIEW, 'groups_view_items[]', $fullList);
		}
		// To Approve
		$groupsCanApproveCheckbox->addOptionArray($groupList);
		$form->addElement($groupsCanApproveCheckbox);
		// To Submit
		$groupsCanSubmitCheckbox->addOptionArray($groupList);
		$form->addElement($groupsCanSubmitCheckbox);
		// To View
		$groupsCanViewCheckbox->addOptionArray($groupList);
		$form->addElement($groupsCanViewCheckbox);
		// To Save
		$form->addElement(new \XoopsFormHidden('op', 'save'));
		$form->addElement(new \XoopsFormButtonTray('', _SUBMIT, 'submit', '', false));
		return $form;
	}

	/**
	 * Get Values
	 * @param null $keys
	 * @param null $format
	 * @param null $maxDepth
	 * @return array
	 */
	public function getValuesItems($keys = null, $format = null, $maxDepth = null)
	{
		$helper  = \XoopsModules\Wgdiaries\Helper::getInstance();
		$utility = new \XoopsModules\Wgdiaries\Utility();
		$ret = $this->getValues($keys, $format, $maxDepth);
		$ret['id']            = $this->getVar('item_id');
		$ret['remarks']       = $this->getVar('item_remarks', 'e');
		$editorMaxchar = $helper->getConfig('editor_maxchar');
		$ret['remarks_short'] = $utility::truncateHtml($ret['remarks'], $editorMaxchar);
		$ret['datefrom']      = \formatTimestamp($this->getVar('item_datefrom'), 'm');
		$ret['dateto']        = \formatTimestamp($this->getVar('item_dateto'), 'm');
		$ret['datecreated']   = \formatTimestamp($this->getVar('item_datecreated'), 's');
		$ret['submitter']     = \XoopsUser::getUnameFromId($this->getVar('item_submitter'));
		return $ret;
	}

	/**
	 * Returns an array representation of the object
	 *
	 * @return array
	 */
	public function toArrayItems()
	{
		$ret = [];
		$vars = $this->getVars();
		foreach (\array_keys($vars) as $var) {
			$ret[$var] = $this->getVar('"{$var}"');
		}
		return $ret;
	}
}
