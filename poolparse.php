#!/usr/bin/php
<?php


$charm=array("\xa0","\x92","\x93","\x94","\x96");
$charr=array(" ","'","\"","\"","-");
$elements=array();
$topics=array();
$questions=array();
while(false !==($line = fgets(STDIN))){
	$line = str_replace($charm,$charr,trim($line));
	if(preg_match('/^([T][0-9])([A-Z])([0-9]{2}) \(([A-D])\) ?(\[(.+)\])?$/',$line,$m)){
		$q=array();
		$q['topic']=$m[1];
		$q['group']=$m[2];
		$q['id']=$m[3];
		$q['answer']=$m[4];
		if(isset($m[6])){
			$q['section']=$m[6];
		}
		while(false !==($line = fgets(STDIN))){
			$line = str_replace($charm,$charr,trim($line));
			if(preg_match('/([A-D])\. (.*)/',$line,$m)){
				$q[$m[1]]=$m[2];
			}elseif($line[0] == '~'){
			    break;
			}else{
				$q['question'] = $line;
			}
		}
		$questions[$q['topic']][$q['group']][$q['id']]=$q;
	}elseif(preg_match('/^SUBELEMENT ([T][0-9]) - (.+) ?-? \[([0-9]) Exam Questions - ([0-9]) Groups\]$/',$line,$m)){
		$s=array();
		$s['topic']=$m[1];
		$s['description']=rtrim(rtrim($m[2],"-")," ");
		$s['questions']=$m[3];
		$s['groups']=$m[4];
		$elements[$s['topic']]=$s;
	}elseif(preg_match('/^([T][0-9])([A-Z]) - (.+)$/',$line,$m)){
		$t=array();
		$t['topic']=$m[1];
		$t['group']=$m[2];
		$t['desctiption']=$m[3];
		$topics[$t['topic']][$t['group']]=$t;
	}
}
$ea=array();
$eq=array();
foreach($questions as $qk=>$qv){
	foreach($qv as $tk=>$tv){
	  	$qnum=rand(1,count($tv));
	  	if($qnum<10){
	  		$qnum="0".$qnum;
  		}
  		$qv=$tv[$qnum];
  		fwrite(STDERR, $qk.$tk.$qv['id'].": ".$qv['answer']."\n");
  		fwrite(STDOUT, $qk.$tk.$qv['id'].": ".$qv['question']."\n    A. ".$qv['A']."\n    B. ".$qv['B']."\n    C. ".$qv['C']."\n    D. ".$qv['D']."\n\n");
	}
}

