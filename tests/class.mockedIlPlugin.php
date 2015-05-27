<?php
/*
 * This is nasty but necessary since the constructor and init function of ilPlugin are final and rely on other
 * parts of ilias
 */
class ilPlugin{}

class mockedIlPlugin extends ilPlugin{

    /**
     * @var string
     */
    protected $module = "";

    /**
     * @var array
     */
    protected $plugin_test_data = array(
        'txt' => array(
        ),
    );

    /**
     * @return MockedIlPlugin
     */
    public static function getPluginObject(){
        return self;
    }

    /**
     * @return null
     */
    public static function lookupNameForId(){
        return null;
    }

    /**
     * @param $keyword
     * @return string
     * @throws Exception
     */
    public function txt($keyword){
        if(!array_key_exists($keyword,$this->getPluginTestData()['txt'])){
            throw new Exception("Invalid Keyword");
        }

        return  $this->getPluginTestData()['txt'][$keyword];
    }

    /**
     * @param array $plugin_test_data
     */
    public function setPluginTestData($plugin_test_data)
    {
        $this->plugin_test_data = $plugin_test_data;
    }

    /**
     * @return array
     */
    public function getPluginTestData()
    {
        return $this->plugin_test_data;
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
}
?>