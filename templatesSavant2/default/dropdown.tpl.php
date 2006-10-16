<select name="<?=$this->inputname?>">
   <option value=""></option>
   <?php foreach($this->values as $value):?>
   <?php
	  $value = trim ($value);
	  unset ($selected);
	  if ($value == $this->post_value)
	  {
		 $selected = 'selected="selected"';
	  }
   ?>
   <option value="<?=trim($value)?>" <?=$selected?>><?=trim($value)?></option>
   <?php endforeach?>
</select>


