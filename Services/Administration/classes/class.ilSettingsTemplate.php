<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

/**
 * Settings template application class
 *
 * @author Alexander Killing <killing@leifos.de>
 * @deprecated
 */
class ilSettingsTemplate
{
    protected ilDBInterface $db;
    private int $id;
    private string $type;
    private string $title;
    private string $description;
    private array $setting = array();
    private array $hidden_tab = array();
    private bool $auto_generated = false;
    private ilSettingsTemplateConfig $config;

    public function __construct(
        int $a_id = 0,
        ?ilSettingsTemplateConfig $config = null
    ) {
        global $DIC;

        $this->db = $DIC->database();
        if ($a_id > 0) {
            if ($config) {
                $this->setConfig($config);
            }
            $this->setId($a_id);
            $this->read();
        }
    }

    public function setId(int $a_val) : void
    {
        $this->id = $a_val;
    }

    public function getId() : int
    {
        return $this->id;
    }
    
    public function setAutoGenerated(bool $a_status) : void
    {
        $this->auto_generated = $a_status;
    }
    
    public function getAutoGenerated() : bool
    {
        return $this->auto_generated;
    }

    public function setTitle(string $a_val) : void
    {
        $this->title = $a_val;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setType(string $a_val) : void
    {
        $this->type = $a_val;
    }

    public function getType() : string
    {
        return $this->type;
    }

    public function setDescription(string $a_val) : void
    {
        $this->description = $a_val;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    /**
     * Set setting
     * @param array|string $a_value
     */
    public function setSetting(
        string $a_setting,
        $a_value,
        bool $a_hide = false
    ) : void {
        if ($this->getConfig()) {
            $settings = $this->getConfig()->getSettings();

            if ($settings[$a_setting]['type'] === ilSettingsTemplateConfig::CHECKBOX) {
                if (is_array($a_value)) {
                    $a_value = serialize($a_value);
                } else {
                    $a_value = unserialize($a_value, ['allowed_classes' => false]);
                }
            }
        }

        $this->setting[$a_setting] = array(
            "value" => $a_value,
            "hide" => $a_hide
        );
    }

    public function removeSetting(string $a_setting) : void
    {
        unset($this->setting[$a_setting]);
    }

    public function removeAllSettings() : void
    {
        $this->setting = array();
    }

    public function getSettings() : array
    {
        return $this->setting;
    }

    public function addHiddenTab(string $a_tab_id) : void
    {
        $this->hidden_tab[$a_tab_id] = $a_tab_id;
    }

    public function removeAllHiddenTabs() : void
    {
        $this->hidden_tab = array();
    }

    public function getHiddenTabs() : array
    {
        return $this->hidden_tab;
    }
    
    /**
     * Returns the template config associated with this template or NULL if
     * none is given.
     */
    public function getConfig() : ?ilSettingsTemplateConfig
    {
        return $this->config;
    }

    /**
     * Sets the template config for this template
     */
    public function setConfig(ilSettingsTemplateConfig $config) : void
    {
        $this->config = $config;
    }

    public function read() : void
    {
        $ilDB = $this->db;

        // read template
        $set = $ilDB->query(
            "SELECT * FROM adm_settings_template WHERE " .
            " id = " . $ilDB->quote($this->getId(), "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        $this->setTitle($rec["title"]);
        $this->setType($rec["type"]);
        $this->setDescription($rec["description"]);
        // begin-patch lok
        $this->setAutoGenerated($rec['auto_generated']);
        // end-patch lok

        // read template setttings
        $set = $ilDB->query(
            "SELECT * FROM adm_set_templ_value WHERE " .
            " template_id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->setSetting(
                $rec["setting"],
                $rec["value"],
                $rec["hide"]
            );
        }

        // read hidden tabs
        $set = $ilDB->query(
            "SELECT * FROM adm_set_templ_hide_tab WHERE " .
            " template_id = " . $ilDB->quote($this->getId(), "integer")
        );
        while ($rec = $ilDB->fetchAssoc($set)) {
            $this->addHiddenTab($rec["tab_id"]);
        }
    }

    public function create() : void
    {
        $ilDB = $this->db;

        $this->setId($ilDB->nextId("adm_settings_template"));

        // write template
        $ilDB->insert("adm_settings_template", array(
            "id" => array("integer", $this->getId()),
            "title" => array("text", $this->getTitle()),
            "type" => array("text", $this->getType()),
            // begin-patch lok
            "description" => array("clob", $this->getDescription()),
            'auto_generated' => array('integer',$this->getAutoGenerated())
            // end-patch lok
            ));

        // write settings
        $this->insertSettings();

        // write hidden tabs
        $this->insertHiddenTabs();
    }

    public function update() : void
    {
        $ilDB = $this->db;

        // update template
        $ilDB->update("adm_settings_template", array(
            "title" => array("text", $this->getTitle()),
            "type" => array("text", $this->getType()),
            // begin-patch lok
            "description" => array("clob", $this->getDescription()),
            'auto_generated' => array('integer',$this->getAutoGenerated())
            ), array(
            "id" => array("integer", $this->getId()),
            ));

        // delete settings and hidden tabs
        $ilDB->manipulate(
            "DELETE FROM adm_set_templ_value WHERE "
            . " template_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $ilDB->manipulate(
            "DELETE FROM adm_set_templ_hide_tab WHERE "
            . " template_id = " . $ilDB->quote($this->getId(), "integer")
        );

        // insert settings and hidden tabs
        $this->insertSettings();
        $this->insertHiddenTabs();
    }

    private function insertSettings() : void
    {
        $ilDB = $this->db;

        foreach ($this->getSettings() as $s => $set) {
            $ilDB->manipulate("INSERT INTO adm_set_templ_value " .
                "(template_id, setting, value, hide) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($s, "text") . "," .
                $ilDB->quote($set["value"], "text") . "," .
                $ilDB->quote($set["hide"], "integer") .
                ")");
        }
    }

    public function insertHiddenTabs() : void
    {
        $ilDB = $this->db;

        foreach ($this->getHiddenTabs() as $tab_id) {
            $ilDB->manipulate("INSERT INTO adm_set_templ_hide_tab " .
                "(template_id, tab_id) VALUES (" .
                $ilDB->quote($this->getId(), "integer") . "," .
                $ilDB->quote($tab_id, "text") .
                ")");
        }
    }

    public function delete() : void
    {
        $ilDB = $this->db;

        $ilDB->manipulate(
            "DELETE FROM adm_settings_template WHERE "
            . " id = " . $ilDB->quote($this->getId(), "integer")
        );
        $ilDB->manipulate(
            "DELETE FROM adm_set_templ_value WHERE "
            . " template_id = " . $ilDB->quote($this->getId(), "integer")
        );
        $ilDB->manipulate(
            "DELETE FROM adm_set_templ_hide_tab WHERE "
            . " template_id = " . $ilDB->quote($this->getId(), "integer")
        );
    }

    /**
     * Get all settings templates of type
     */
    public static function getAllSettingsTemplates(
        string $a_type,
        bool $a_include_auto_generated = false
    ) : array {
        global $DIC;

        $ilDB = $DIC->database();

        if ($a_include_auto_generated) {
            $set = $ilDB->query("SELECT * FROM adm_settings_template " .
                " WHERE type = " . $ilDB->quote($a_type, "text") .
                " ORDER BY title");
        } else {
            $set = $ilDB->query("SELECT * FROM adm_settings_template " .
                " WHERE type = " . $ilDB->quote($a_type, "text") .
                'AND auto_generated = ' . $ilDB->quote(0, 'integer') . ' ' .
                " ORDER BY title");
        }

        $settings_template = array();
        while ($rec = $ilDB->fetchAssoc($set)) {
            $settings_template[] = $rec;
        }
        return $settings_template;
    }

    protected static function lookupProperty(
        int $a_id,
        string $a_prop
    ) : string {
        global $DIC;

        $ilDB = $DIC->database();

        $set = $ilDB->query(
            "SELECT $a_prop FROM adm_settings_template WHERE " .
            " id = " . $ilDB->quote($a_id, "integer")
        );
        $rec = $ilDB->fetchAssoc($set);
        return $rec[$a_prop];
    }

    public static function lookupTitle(int $a_id) : string
    {
        return self::lookupProperty($a_id, 'title');
    }

    public static function lookupDescription(int $a_id) : string
    {
        return self::lookupProperty($a_id, 'description');
    }
    
    public static function translate(string $a_title_desc) : string
    {
        global $DIC;

        if (str_starts_with($a_title_desc, 'il_')) {
            return $DIC->language()->txt($a_title_desc);
        }
        return $a_title_desc;
    }
}
