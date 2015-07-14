<?php

$input_fields = [
    'time'=>'raw',
    'days'=>'float',
    'prebun'=>'float',
    'postbun'=>'float',
    'uf'=>'float',
    'postweight'=>'float',
    'age'=>'int',
    'height'=>'raw',
    'sex'=>'string',
    'african_american'=>'nullbool',
    'diabetes'=>'nullbool',
    'access_type'=>'string',
    'schedule_treatments'=>'int',
    'schedule_duration'=>'int'
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
    $return_type = "json";
}
elseif (isset($_GET) && isset($_GET['prebun']))
{
    $input = $_GET;
    $return_type = "redirect";
}

foreach ($input_fields as $name => $type)
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
if (strpos($time_raw, ':') !== FALSE) // hh:mm (:ss ignored)
{
	$time_elements = explode(':', $time_raw);
	$time_hours = filter_var($time_elements[0], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$time_minutes = filter_var($time_elements[1], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
	$time = $time_hours * 60 + $time_minutes;

}
else { // guess at hours vs. minutes
    if ($time <= 10) // hours
        $time = filter_var($input['time'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_NULL_ON_FAILURE | FILTER_FLAG_ALLOW_FRACTION) * 60.0;
    else // minutes
        $time = filter_var($input['time'], FILTER_SANITIZE_NUMBER_INT, FILTER_NULL_ON_FAILURE);
}
$time = $time/60.0; // hours

if (isset($input['height']))
{
    $height_raw = $input['height'];
    $foot_pos = strpos($height_raw, "'");
    if ($foot_pos !== FALSE)
    {
        $inch_pos = strpos($height_raw, '"');
        if ($inch_pos === FALSE)
            $inch_pos = strlen($height_raw);
        $height_feet = substr($height_raw, 0, $foot_pos);
        if ($foot_pos+1 != strlen($height_raw)) // allow for X' without inches
            $height_inches = substr($height_raw, $foot_pos+1, $inch_pos-$foot_pos-1);
        else
            $height_inches = 0;
        $height = $height_feet*12 + $height_inches;
        $height = round($height * 2.54); // convert to cm
    }
    else
    {
        $height = round($height_raw);
        if ($height < 100) // assume no one is shorter than 3.5 ft
            $height = round($height * 2.54); // convert to cm
        // otherwise, cm as entered
    }
}

$days = $input['days'];
$prebun = $input['prebun'];
$postbun = $input['postbun'];
$uf = $input['uf'];
$postweight = $input['postweight'];
$age = $input['age'];
    // or birthdate
$sex = $input['sex'];
$african_american = $input['african_american'];
$diabetes = $input['diabetes'];
$access_type = $input['access_type'];
if ($access_type === 'cath')
	$C = 22.0; // Central catheter constant
elseif ($access_type === 'avf')
	$C = 35.0; // AVF access constant


/* Calculations */
if (isset($input['ureavolume']) && $input['ureavolume'] !== '')
{
    $water_volume = $input['ureavolume'];
    $tbw_type = "Manual";
}
// Estimate Total Body Water
elseif (isset($height) && isset($sex) && (isset($age) || $sex == 'female'))
{
    // Watson
    if ($sex === 'male') {
        $water_volume = 2.447 - (0.09516 * $age) + 0.1074 * $height + 0.3362 * $postweight;
        $tbw_type = "Watson, male";
    }
    else {
        $water_volume = -2.097 + (0.2466 * $postweight) + (0.1069 * $height);
        $tbw_type = "Watson, female";
    }

    //if ($african_american !== NULL && $diabetes !== NULL)
    //{
        // Anthropometrically estimated total body water volumes are larger than modeled urea volume...
        // http://www.nature.com/ki/journal/v64/n3/full/4493991a.html
        $water_volume = $water_volume * 0.824 * (($sex==='male' ? 0.998 : 0.985) * max(1, $age-50)) * ($sex==='male' ? 1 : 1.033) * ($african_american ? 1.043 : 1) * ($diabetic ? 1.033 : 1);
        $tbw_type = "Daugirdas urea volume";
    //}
}
else
{
    // Where did I get this one?? (extracellular water?)
    $water_volume = .2295 * $postweight * 3;
    $tbw_type = "weight only";
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

if ($return_type === "json")
{
    header("Content-type: application/json");
    $return = array(
        'std' => $stdKtV,
        'short_sp' => $short_break_spKtV,
        'long_sp' => $long_break_spKtV,
        'avg_sp' => $spKtV,
        'eKtV' => $eKtV,
        'time' => $time,
        'tbw_type' => $tbw_type,
        'height' => $height,
        'tbw' => $water_volume
    );
    echo json_encode($return);
}
elseif ($return_type === "redirect")
{
	header("Location: /ktv.php?std=$stdKtV&avg_sp=$spKtV");
}
else
{
    header("X_BIGPROBLEM: Last else in calculate.php");
}

?>
