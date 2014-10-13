<?php

$input_fields = [
    'time'=>'raw',
    'days'=>'float',
    'prebun'=>'float',
    'postbun'=>'float',
    'uf'=>'float',
    'postweight'=>'float',
    'age'=>'int',
    'height'=>'int',
    'sex'=>'string',
    'african_american'=>'nullbool',
    'diabetes'=>'nullbool',
    'access_type'=>'string'
];

function validate($type, $value, $nonempty=TRUE)
{
    if ($nonempty && (!isset($value) || $value === '' || $value === NULL))
        return NULL;

    switch ($type)
    {
        case 'int':
            return filter_var($value, FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
            break;
        case 'float':
            return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION);
            break;
        case 'nullbool':
        case 'bool':
            return $value === 'true' ? TRUE : FALSE;
            break;
        case 'string':
            return filter_var($value, FILTER_SANITIZE_STRING, FILTER_NULL_ON_FAILURE);
            break;
        case 'raw':
        default:
            return $value;
            break;
    }
}


if (isset($_POST) && isset($_POST['prebun']))
{
    $input = $_POST;
    $type = "json";
}
elseif (isset($_GET) && isset($_GET['prebun']))
{
    $input = $_GET;
    $type = "redirect";
}

foreach ($input_field as $name => $type)
{
    if (!isset($input[$name]))
        $input[$name] = NULL;
    $input[$name] = validate($type, $input[$name]);
}

// -- Add special processing and helpful non-binary error returns for $sex --
// e.g. "Sex must be Male, Female, or undefined. Physiological sex is determined partially
// by anatomy and partially by hormones. It affects the distribution of body water. Choose
// the sex that better matches the patient's physiological gender status, or neither."

// Special processing for time formats
$time_raw = $input['time'];
if (strpos($time, ':') !== FALSE) // hh:mm (:ss ignored)
{
	$time_elements = explode(':', $time_raw);
	$time_hours = filter_var($time_elements[0], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$time_minutes = filter_var($time_elements[1], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$time = $time_hours * 60 + $time_minutes;
}
else // in minutes
	$time = filter_var($input['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
$time = $time/60; // hours

$days = $input['days'];
$prebun = $input['prebun'];
$postbun = $input['postbun'];
$uf = $input['uf'];
$postweight = $input['postweight'];
$age = $input['age'];
    // or birthdate
$height = $input['height'];
    // heuristic: inches vs cm
$sex = $input['sex'];
$african_american = $input['african_american'];
$diabetes = $input['diabetes'];
$access_type = $input['access_type'];
if ($access_type === 'cath')
	$C = 22.0; // Central catheter constant
elseif ($access_type === 'avf')
	$C = 35.0; // AVF access constant


/* Calculations */

// Estimate Total Body Water
if ($height)
{
    // Watson
    if ($sex)
    {
        if ($sex === 'male')
            $water_volume = 2.447 - (0.09516 * $age) + 0.1074 * $height + 0.3362 * $postweight;
        else
            $water_volume = -2.097 + (0.2466 * $postweight) + (0.1069 * $height);

        if ($african_american !== NULL && $diabetes !== NULL)
        {
            // Anthropometrically estimated total body water volumes are larger than modeled urea volume...
            // http://www.nature.com/ki/journal/v64/n3/full/4493991a.html
            $water_volume = $water_volume * 0.824 * (($sex==='male' ? 0.998 : 0.985) * max(0, $age-50)) * ($sex==='male' ? 1 : 1.033) * ($african_american ? 1.043 : 1) * ($diabetic ? 1.033 : 1);
        }
    }
}
else
{
    // Where did I get this one?? (extracellular water?)
    $water_volume = .2295 * $postweight * 3;
}

// Improved equation for estimating single-pool Kt/V at higher dialysis frequencies
// https://clinicalresearch.ccf.org/fhn/webdocs/publications/Daugirdas%20Improved%20KtV%20pred%20NDT%202012.pdf
$R = $postbun/$prebun;
$short_break_GFAC = 0.0174 / (floor(7/$days) - $time/24);
$long_break_GFAC = 0.0174 / (ceil(7/$days) - $time/24);
$avg_GFAC = 0.0174 / (7/$days - $time/24);
$short_break_spKtV = -log($R - $short_break_GFAC * $time) + (4 - 3.5*$R) * 0.55 * $uf/$water_volume;
$long_break_spKtV = -log($R - $long_break_GFAC * $time) + (4 - 3.5*$R) * 0.55 * $uf/$water_volume;
$spKtV = -log($R - $avg_GFAC * $time) + (4 - 3.5*$R) * 0.55 * $uf/$water_volume;

// Calculation of Standard Kt/V (stdKt/V) with Corrections for Postdialysis Urea Rebound
// http://onlinelibrary.wiley.com/doi/10.1046/j.1492-7535.2003.01216.x/abstract
//$eKtV = 0.924 * $spKtV - 0.395 * $spKtV/$time + 0.056; // but this one??
$eKtV = 0.927 * $spKtV - 0.255 * $spKtV/$time;
$stdKtV = ((168*(1-exp(-$eKtV)))/$time)/(((1-exp(-$eKtV))/$spKtV)+(168/($days*$time))-1);
    // 10080 = minutes in a week
    // 168 = hours in a week
// Had this other one in, but I don't know where I got it
//$eKtV = 0.924 * $spKtV - 0.395 * $spKtV/$time + 0.056;

if ($type === "json")
{
    header("Content-type: application/json");
    $return = array();
    $return['std'] = $stdKtV;
    $return['short_sp'] = $short_break_spKtV;
    $return['long_sp'] = $long_break_spKtV;
    $return['avg_sp'] = $spKtV;
    echo json_encode($return);
}
elseif ($type === "redirect")
{
	header("Location: /ktv.php?std=$stdKtV&short_sp=$short_break_spKtV&long_sp=$long_break_spKtV&avg_sp=$spKtV");
}

?>
