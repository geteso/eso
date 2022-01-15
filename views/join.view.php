<?php
/**
 * This file is part of the eso project, a derivative of esoTalk.
 * It has been modified by several contributors.  (contact@geteso.org)
 * Copyright (C) 2022 geteso.org.  <https://geteso.org>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * Join view: displays an interface enabling users to sign up as members.
 */
if(!defined("IN_ESO"))exit;
?>

<?php
// If the user can't sign up, inform them with an error message.
if(($error=$this->canJoin())!==true):
echo $this->eso->htmlMessage($error);

// If they can, show the form.
else:
?>

<form action='<?php echo makeLink("join");?>' method='post' id='join'>
	
<?php
// Loop through the fieldsets in the form.
foreach($this->form as $id=>$fieldset):
    if(is_array($fieldset)):
        echo "<fieldset id='$id'><legend>{$fieldset["legend"]}</legend><ul class='form'>";
        ksort($fieldset);
        
        // Loop through the fields in the fieldsets.
        foreach($fieldset as $k=>$field):
            if($k==="legend")continue;
            if(is_array($field)):
                echo "<li>{$field["html"]} <div id='{$field["id"]}-message'>";
                if(!empty($field["message"]))echo $this->eso->htmlMessage($field["message"]);
                echo "</div></li>";
            else:echo $field;endif;
        endforeach;
        
        echo "</ul></fieldset>";
    else:echo $fieldset;endif;
endforeach;
?>

<p><?php echo $this->eso->skin->button(array("id"=>"joinSubmit","value"=>$language["Join this forum"],"class"=>"big","tabindex"=>1000));?></p>

<script type='text/javascript'>
// <![CDATA[
// Construct a JavaScript array of the fields in the form.
Join.fieldsValidated = {<?php
$fieldsValidated=array();
foreach($this->fields as $field){
    if(!empty($field["ajax"]))
        $fieldsValidated[]="'{$field["id"]}':".((@$field["required"] and !@$field["success"])?"false":"true");
}
echo implode(",",$fieldsValidated);
?>};
Join.init();
// ]]>
</script>

</form>

<?php endif;?>
