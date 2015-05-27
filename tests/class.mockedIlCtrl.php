<?php
class ilCtrl{}

class mockedIlCtrl extends ilCtrl{
    /**
     * @var array
     */
    var $save_parameter = array();

    public function setParameterByClass($a_class, $a_parameter, $a_value)
    {
        $this->parameter[strtolower($a_class)][$a_parameter] = $a_value;
    }

    function getLinkTargetByClass($a_class, $a_cmd  = "", $a_anchor = "", $a_asynch = false, $xml_style = true)
    {
        return "MockedLinkTarget: class=".$a_class." cmd=".$a_cmd." a_anchor=".$a_anchor." a_asynch=".$a_asynch." xml_style=".$xml_style;
    }


}
?>