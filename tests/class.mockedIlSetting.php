<?php
class ilSetting{}

class ilMockedTestSetting{
    /**
     * @var string
     */
    protected  $module = "";

    /**
     * @var array
     */
    protected $settings_test_data = array(
        'common' => array(
            "disable_comments"=>false,
            "disable_notes"=>false,
        ),
        'tags' => array(
            "enable"=>false
        ),
    );

    function ilSetting($module = "common"){
        $this->setModule($module);
    }

    public function get($keyword = "disable_comments"){
        if(!array_key_exists($this->getModule(),$this->getSettingsTestData())){
            throw new Exception("Invalid Module");
        }
        if(!array_key_exists($this->getModule(),$this->getSettingsTestData())){
            throw new Exception("Invalid Keyword");
        }
        return  $this->settings_test_data[$this->module][$keyword];
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param array $settings_test_data
     */
    public function setSettingsTestData($settings_test_data)
    {
        $this->settings_test_data = $settings_test_data;
    }

    /**
     * @return array
     */
    public function getSettingsTestData()
    {
        return $this->settings_test_data;
    }
}
?>