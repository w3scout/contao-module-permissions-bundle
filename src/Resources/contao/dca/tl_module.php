<?php

/**
 * module_permissions extension for Contao Open Source CMS
 *
 * Copyright (C) 2013 Codefog
 *
 * @package module_permissions
 * @author  Codefog <http://codefog.pl>
 * @author  Kamil Kuzminski <kamil.kuzminski@codefog.pl>
 * @license LGPL
 */


/**
 * Add the "onload_callback" to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('tl_module_permissions', 'checkPermission');
$GLOBALS['TL_DCA']['tl_module']['config']['onsubmit_callback'][] = array('tl_module_permissions', 'addPermissions');


/**
 * Class tl_module_permissions
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_module_permissions extends Contao\Backend
{

    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    protected function hasLimitedPermissions()
    {
        return (!$this->User->isAdmin && is_array($this->User->feModules) && !empty($this->User->feModules));
    }

    /**
     * Check the permissions
     */
    public function checkPermission()
    {
        if ($this->hasLimitedPermissions()) {
            $GLOBALS['TL_DCA']['tl_module']['list']['sorting']['root'] = $this->User->feModules;
        }
    }

    public function getModules() {
        die("getModules");
        if ($this->hasLimitedPermissions()) {
            return $this->User->feModules;
        }

        $arrModules = array();
		$objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id ORDER BY t.name, m.name");
		while ($objModules->next())
		{
			$arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
		}
		return $arrModules;
    }

    public function addPermissions()
    {
        if (!$this->hasLimitedPermissions()) { // no restrictions -> nothing to do
            return;
        }

        // Dynamically add the record to the user profile
        if (\Input::get('act') == 'edit' && !in_array(\Input::get('id'),
                $GLOBALS['TL_DCA']['tl_module']['list']['sorting']['root'])) {
            $arrNew = $this->Session->get('new_records');

            if (is_array($arrNew['tl_module']) && in_array(\Input::get('id'), $arrNew['tl_module'])) {
                // Add permissions on user level
                if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                    $objUser = $this->Database->prepare("SELECT feModules FROM tl_user WHERE id=?")
                        ->limit(1)
                        ->execute($this->User->id);

                    if ($objUser->numRows) {
                        $arrModules = deserialize($objUser->feModules);
                        $arrModules[] = \Input::get('id');

                        $this->Database->prepare("UPDATE tl_user SET feModules=? WHERE id=?")
                            ->execute(serialize($arrModules), $this->User->id);
                    }
                } // Add permissions on group level
                elseif ($this->User->groups[0] > 0) {
                    $objGroup = $this->Database->prepare("SELECT feModules FROM tl_user_group WHERE id=?")
                        ->limit(1)
                        ->execute($this->User->groups[0]);

                    if ($objGroup->numRows) {
                        $arrModules = deserialize($objGroup->feModules);
                        $arrModules[] = \Input::get('id');

                        $this->Database->prepare("UPDATE tl_user_group SET feModules=? WHERE id=?")
                            ->execute(serialize($arrModules), $this->User->groups[0]);
                    }
                }

                // Add new element to the user object
                $GLOBALS['TL_DCA']['tl_module']['list']['sorting']['root'][] = \Input::get('id');
                $this->User->feModules = $GLOBALS['TL_DCA']['tl_module']['list']['sorting']['root'];
            }
        }
    }
}
