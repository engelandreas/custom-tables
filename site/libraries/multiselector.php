<?php
/**
 * CustomTables Joomla! 3.x Native Component
 * @author Ivan komlev <support@joomlaboat.com>
 * @link http://www.joomlaboat.com
 * @license GNU/GPL
 **/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_SITE.DIRECTORY_SEPARATOR.'administrator'.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_customtables'.DIRECTORY_SEPARATOR.'libraries'.DIRECTORY_SEPARATOR.'misc.php');

class ESMultiSelector
{
	function getMultiString($parent, $prefix)
	{
		$parentid=$this->es->getOptionIdFull($parent);
		$a=$this->getMultiSelector($parentid,$parent, $prefix);
		return implode(',',$a);

	}

	function getMultiSelector($parentid,$parentname,$langpostfix,$ObjectName,&$ItemList,&$count,$field_value)
	{
		$result='';
		$rows=$this->getList($parentid, $langpostfix);

		if(count($rows)<1)
			return "";

		$result.='<ul>';
		$IDs=array();

		$count=count($rows);
		foreach($rows as $row)
		{

			$IDs[]=$row->id;

			$temp_Ids="";
			$count_child=0;

			if(strlen($parentname)==0)
				$optionnamefull=$row->optionname;
			else
				$optionnamefull=$parentname.'.'.$row->optionname;


			$ChildHTML=$this->getMultiSelector($row->id,$optionnamefull,$langpostfix,$ObjectName,$temp_Ids,$count_child,$field_value);




			if($count_child>0)
			{
				if((strpos($field_value,$optionnamefull.'.')===false))
				{
					$result.='<li><input type="checkbox" id="'.$ObjectName.'_'.$row->id.'" name="'.$ObjectName.'_'.$row->id.'" onClick=\'CustomTablesChildClick("'.$ObjectName.'_'.$row->id.'", "div'.$ObjectName.'_'.$row->id.'")\'  >';
					$result.='<b>'.$row->title.'</b> ('.$count_child.')';
					$result.='<div name="div'.$ObjectName.'_'.$row->id.'" id="div'.$ObjectName.'_'.$row->id.'" style="display: none;">';
				}
				else
				{
					$result.='<li><input type="checkbox" id="'.$ObjectName.'_'.$row->id.'" name="'.$ObjectName.'_'.$row->id.'" onClick=\'CustomTablesChildClick("'.$ObjectName.'_'.$row->id.'", "div'.$ObjectName.'_'.$row->id.'")\' checked="checked" >';
					$result.='<b>'.$row->title.'</b> ('.$count_child.')';
					$result.='<div name="div'.$ObjectName.'_'.$row->id.'" id="div'.$ObjectName.'_'.$row->id.'" style="display: block;">';
				}

				if($count_child>1)
				{
						$result.='
				<div style="margin-left:100px">
				<a href=\'javascript:ESCheckAll("'.$ObjectName.'",Array('.$temp_Ids.'))\'>'.JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_CHECK_ALL' ).'</a>&nbsp;&nbsp;
				<a href=\'javascript:ESUncheckAll("'.$ObjectName.'",Array('.$temp_Ids.'))\'>'.JoomlaBasicMisc::JTextExtended('COM_CUSTOMTABLES_UNCHECK_ALL' ).'</a>
				</div>';
				}

					$result.=$ChildHTML.'</div></li>';

			}
			else
			{


				if((strpos($field_value,$parentname.'.'.$row->optionname.'.')===false))
					$ItemSelected=false;
				else
					$ItemSelected=true;

				$result.='<li><input type="checkbox" name="'.$ObjectName.'_'.$row->id.'" id="'.$ObjectName.'_'.$row->id.'" '.($ItemSelected ? ' checked="checked" ' :'').'> '.$row->title.'</li>';
			}


		}
		$result.='</ul>';

		$ItemList='"'.implode('","',$IDs).'"';


		return $result;
	}





	function getList($parentid, $langpostfix) {


     $db = JFactory::getDBO();

     $query = 'SELECT id, optionname, title'.$langpostfix.' AS title FROM #__customtables_options WHERE parentid='.(int)$parentid;

     $query.=' ORDER BY ordering, title';

		$db->setQuery($query);
				if (!$db->query())	die( $db->stderr());

		return $db->loadObjectList();
	}
}
