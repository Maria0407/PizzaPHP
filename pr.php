<?
abstract class Money {
    public $val; 
    abstract public function created();
    }

class Loan extends Money {
    public $dat;
    public $d; //duration
    public function created(){
        echo "Loan created!";
    }
}

class Payment extends Money {
    public $dat;
    public function created(){
        echo "Payment created!";
    }
}

class Percent extends Money {
    public $p;
    public function created(){
        echo "Percent created!";
    }
}

class Debt extends Money {
    public function created(){
        echo "Percent created!";
    }
}
//считает следующий день
function nextday($date){
    return $date->modify('+1 day');
}

function readinputdata($f,&$lo,&$pa,&$pe){
    $f=fopen('data.txt',"r") or die("File not found!");
    //reading loan
    $data=fgets($f);
    $a0=explode(" ",$data);
    $lo->val=$a0[0]; $lo->dat=new DateTime($a0[1]);
    //reading % & duration
    $data=fgets($f);
    $pe->p=$data;
    $data=fgets($f);
    $lo->d=$data;
    //reading pays
    $data=fgets($f);
    $a0=explode(" ",$data); $i=0; $k=count($a0)/2;
    while ($i<$k) {
        $pa[$i]= new Payment;
        $pa[$i]->val=$a0[2*$i];
        $pa[$i]->dat=new DateTime($a0[2*$i+1]);
        $i++;
    }
    fclose($f);
}

function searchpay($d,$ps){
    $i=0;
    while (($d!=$ps[$i]->dat)&&($i<count($ps)))
        {$i++;}
    return $i==count($ps) ? -1 : $i;
}

//счет и циклич. вывод
function counting(&$f,&$base,&$per,$pays,&$itogo,$atDate){
    $dater=$base->dat; $itogo->val=$base->val; $itogo->dat=$base->dat;
    $pay=0; $per->val=$base->val*$per->p; $i=0;
    fprintf($f,"%20s",$dater->format('Y-m-d'));
    fprintf($f,"%20s",$base->val);
    fprintf($f,"%20s",0);
    fprintf($f,"%20s",$pay);
    fprintf($f,"%22s",$itogo->val."\r\n");
    //идем по дням
    while ($dater<$atDate){
         $dater=nextday($dater);
         fprintf($f,"%20s",$dater->format('Y-m-d'));
         fprintf($f,"%20s",$base->val);
         fprintf($f,"%20s",$per->val);
         $itogo->val=$base->val+$per->val;
         //если в массиве платежей нашелся платеж на текущую дату $dater
         if(searchpay($dater,$pays)>-1){
            //пересчитываем результаты
            $pay=$pays[$i]->val;
            $itogo->val=$itogo->val-$pay;
            $base->val=$itogo->val; 
            $per->val=$base->val*$per->p;
            $i++;
            //столбец ИТОГО стал отрицательным - платить больше не надо => и считать больше не надо
            if ($itogo->val<0){
                fprintf($f,"%20s",$pay);
                fprintf($f,"%22s",$itogo->val."\r\n"); 
                fprintf($f,"You have no debts anymore!");
                break 1;
            }
         }
         //если в этот день не вносили платеж
         else{
            $pay=0; 
            $per->val=$per->val+$base->val*$per->p;
         }
         //если процент стал втрое больше основного долга, ограничиваем его
         if ($per->val>=$base->val*3){
            $per->val=$base->val*3; $itogo->val=$base->val+$per->val;
         }
        fprintf($f,"%20s",$pay);
        fprintf($f,"%22s",$itogo->val."\r\n");    
    }
}

$loan = new Loan;
$pays = array();
$per = new Percent;
$debt = new Debt;
readinputdata($in,$loan,$pays,$per);
$at = readline("Enter an atDate: ");
$atDate = new DateTime($at);
//пока не введут допустимую дату, не далее, чем duration от даты займа
while ($atDate->diff($loan->dat)->days>$loan->d)
{
    echo "Wrong date! Try again: ";
    $at = readline("Enter an atDate: ");
    $atDate = new DateTime($at);
}
$out = fopen("rez.txt","w");
fprintf($out,"%20s",'date');
fprintf($out,"%20s",'base');
fprintf($out,"%20s",'percents');
fprintf($out,"%20s",'payment');
fprintf($out,"%22s",'debt'."\r\n");
counting($out,$loan,$per,$pays,$debt,$atDate);
fclose($out);

?>