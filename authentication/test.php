<?php
function demo($x){
    $res =" ";
    for($i=0; $i < $x; $i++){
        $res .=$x;
    }
    return $res;
}
$x = $_POST['x'];
echo demo($x);
?>