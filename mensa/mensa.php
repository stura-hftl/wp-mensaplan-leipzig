<?php
    
/*
Plugin Name: Mensaplan
Plugin URI: http://stura.hftl.de
Description: Fuegt die Moeglichkeit den Speiseplan des Studentenwerkes Leipzig mittels [mensaplan] einzubinden.
Version: 1.0
Author: Tilmann Bach
Author URI: http://laufwerkc.de
Credits: Oliver Bühler
*/

    function insert_mensaplan($content) 
    {
    return str_replace('[mensaplan]', '<div class="mensa-plan">'.get_form().get_mensaplan().'</div>', $content);
    }

    add_filter('the_content', insert_mensaplan);
    
    function get_form()
    {
    $form = '<form class="mensa-dayselector" action="' . str_replace( '%7E', '~', $_SERVER['REQUEST_URI']) . '"  accept-charset="UTF-8" method="post">';
    $form .= '<fieldset class="mensa-location">';
    $form .= '<div class="form-item">';
    $form .= '<select name="mensa_date" class="mensa-form-select">';
        $tage = array("Sonntag","Montag","Dienstag","Mittwoch","Donnerstag","Freitag","Samstag");  			//Deutsche Datumsausgabe
        for ($z=0;$z<=14;$z++)																					//Schleife für 14 Wochentage
        {
            $nextday        = mktime(0, 0, 0, date("m")  , date("d")+$z, date("Y"));							//Definition nächster Tag
            if (!(date(N,$nextday) == '6' OR date(N,$nextday) == '7'))											//Check Wochenende
            {  
                $form .= '<option value="'.date("Ymd",$nextday).'">'.$tage[date("w",$nextday)].date(" - d.m.Y",$nextday).'</option>';
            }
        }
     $form .= '</select>';
    $form .= '<input type="submit" name="op" id="edit-submit" value="anzeigen"  class="form-submit" />';
    $form .= '</div>';
    $form .= '</fieldset>';
    $form .= '</form>';

    return $form;
    }

function get_mensaplan()
{
	
	//Formular Request Handling
	if (isset($_POST['mensa_date'])){
		$request_date = htmlspecialchars($_POST['mensa_date']);
	} else {
            $day = mktime(0, 0, 0, date("m")  , date("d"), date("Y"));
            if (date(N,$day) == '6')
            {  
                $day = mktime(0, 0, 0, date("m")  , date("d")+2, date("Y"));
            }
            elseif (date(N,$day) == '7')
            {  
                $day = mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
            }
		$request_date = urlEncode(date("Ymd",$day));
	}
	
	$request_xml = "http://stura:sturahftl2011@www.studentenwerk-leipzig.de/XMLInterface/request?location=118&date=".$request_date;
	
	//XML Omport
	$xml = simplexml_load_file($request_xml,"SimpleXMLElement",LIBXML_NOCDATA);
	
	//Verfügbarkeitscheck
	if (!isset($xml->group[0])) {
		$mensaplan = "Kein Essensplan verfügbar";
		return $mensaplan;
	}
	

    $mensaplan = "";
	//Schleife für jedes Essen
	for($i=0;$i<=(count($xml->group)-1);$i++){

$mensaplan .= '<div class="mensa-item">';
$mensaplan .= '	<h1>'.$xml->group[$i]->name.'</h1>';
setlocale (LC_MONETARY, 'de_DE.UTF-8', 'de_DE@euro', 'de_DE', 'de', 'ge');
$mensaplan .= '	<p class="mensa-price">'.money_format('%^-14#4.2n', floatval($xml->group[$i]->prices->price[0])).'</p>';
$mensaplan .= '	<ul>';

                    $component_max = count($xml->group[$i]->components->component);
                    for($n=0;$n<$component_max;$n++)
                    {
                        $mensaplan .= '		<li>'.$xml->group[$i]->components->component[$n]->name1.'</li>'; 
                    }
$mensaplan .= '			</ul>';
$mensaplan .= '</div>';
    }
return $mensaplan;
}

?>
