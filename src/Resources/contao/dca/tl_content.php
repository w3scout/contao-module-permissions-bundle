<?php

$GLOBALS['TL_DCA']['tl_content']['fields']['module']['options_callback'] = array(
    'tl_content_module_permissions',
    'getModules'
);

class tl_content_module_permissions extends Contao\Backend
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

    public function getModules()
    {
        $arrModules = array();
        $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id ORDER BY t.name, m.name");
        if ($this->hasLimitedPermissions()) {
            $objModules = $this->Database->execute("SELECT m.id, m.name, t.name AS theme FROM tl_module m LEFT JOIN tl_theme t ON m.pid=t.id WHERE m.id IN(".implode(',',$this->User->feModules).") ORDER BY t.name, m.name");
        }

        while ($objModules->next()) {
            $arrModules[$objModules->theme][$objModules->id] = $objModules->name . ' (ID ' . $objModules->id . ')';
        }
        return $arrModules;
    }
}
