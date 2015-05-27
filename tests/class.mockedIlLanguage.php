<?php
/*
 * This is nasty but necessary since the constructor and init function of ilPlugin are final and rely on other
 * parts of ilias
 */
class ilLanguage{}

class mockedIlLanguage extends ilLanguage{
    /**
     * @var array
     */
    protected $language_test_data = array();

    public function txt($keyword){
        if(!array_key_exists($keyword,$this->getLanguageTestData())){
            throw new Exception("Invalid Keyword");
        }
        return  $this->getLanguageTestData()[$keyword];
    }

    /**
     * @param array $language_test_data
     */
    public function setLanguageTestData($language_test_data)
    {
        $this->language_test_data = $language_test_data;
    }

    /**
     * @return array
     */
    public function getLanguageTestData()
    {
        return $this->language_test_data;
    }


}
?>