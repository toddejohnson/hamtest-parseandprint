<?php

class hamtest {
	private	$questions=array();
	private $maxQuestionPerGroup=0;
	private $minQuestionPerGroup=9999;
	public function parseInput($file=STDIN){
		$charm=array("\xa0","\x92","\x93","\x94","\x96");
		$charr=array(" ","'","\"","\"","-");
		
		while(false !==($line = fgets($file))){
			$line = str_replace($charm,$charr,trim($line));
			if(preg_match('/^([T][0-9])([A-Z])([0-9]{2}) \(([A-D])\) ?(\[(.+)\])?$/',$line,$m)){
				$q=array();
				$topic=$m[1];
				$group=$m[2];
				$id=$m[3];
				$q['answer']=$m[4];
				if(isset($m[6])){
					$ruleSection=$m[6];
				}
				while(false !==($line = fgets($file))){
					$line = str_replace($charm,$charr,trim($line));
					if(preg_match('/([A-D])\. (.*)/',$line,$m)){
						$q[$m[1]]=$m[2];
					}elseif($line[0] == '~'){
						break;
					}else{
						$q['question'] = $line;
					}
				}
				$this->questions[$topic][$group][$id]=$q;
			}elseif(preg_match('/^SUBELEMENT ([T][0-9]) - (.+) ?-? \[([0-9]) Exam Questions - ([0-9]) Groups\]$/',$line,$m)){
				$q=array();
				$topic=$m[1];
				$q['description']=rtrim(rtrim($m[2],"-")," ");
				$q['questions']=$m[3];
				$q['groups']=$m[4];
				$this->questions[$topic]['description']=$q;
			}elseif(preg_match('/^([T][0-9])([A-Z]) - (.+)$/',$line,$m)){
				$q=array();
				$topic=$m[1];
				$group=$m[2];
				$this->questions[$topic][$group]['description']=$m[3];
			}
		}
		foreach($this->questions as $tk=>$tv){
			foreach($tv as $gk=>$gv){
				if($gk=='description') continue;
				$ct=count($gv)-1;

				if($ct>$this->maxQuestionPerGroup){
					$this->maxQuestionPerGroup=$ct;
				}
				if($ct<$this->minQuestionPerGroup){
					$this->minQuestionPerGroup=$ct;
				}
			}
		}
	}
	public function questions($file=STDOUT){
		foreach($this->questions as $tk=>$tv){
			foreach($tv as $gk=>$gv){
				if($gk=='description') continue;
				foreach($gv as $qk=>$qv){
					if($qk=='description') continue;
  					fwrite($file, $tk.$gk.$qk.": ".$qv['question']."\n    A. ".$qv['A']."\n    B. ".$qv['B']."\n    C. ".$qv['C']."\n    D. ".$qv['D']."\n\n");
				}
			}
		}
	}
	public function answers($file=STDERR,$qa=false){
		foreach($this->questions as $tk=>$tv){
			foreach($tv as $gk=>$gv){
				if($gk=='description') continue;
				foreach($gv as $qk=>$qv){
					if($qk=='description') continue;
					if($qa){
					  	fwrite($file, $tk.$gk.$qk.": ".$qv['question']."\n    ".$qv['answer'].". ".$qv[$qv['answer']]."\n\n");
					}else{
						fwrite($file, $tk.$gk.$qk.": ".$qv['answer']."\n");
					}
				}
			}
		}
	}
	public function rndQuiz(){
		foreach($this->questions as $tk=>$tv){
			foreach($tv as $gk=>$gv){
				if($gk=='description') continue;
				$qnum=rand(1,count($gv)-1);
				if($qnum<10)$qnum="0".$qnum;
				$qv=$gv[$qnum];
				fwrite(STDERR, $tk.$gk.$qnum.": ".$qv['answer']."\n");
				fwrite(STDOUT, $tk.$gk.$qnum.": ".$qv['question']."\n    A. ".$qv['A']."\n    B. ".$qv['B']."\n    C. ".$qv['C']."\n    D. ".$qv['D']."\n\n");
			}
		}
	}
	public function iterativeQuiz(){
		for($i=1;$i<=14;$i++){
			$qfile=fopen("question$i.txt","w");
			$afile=fopen("answer$i.txt","w");
			fwrite($qfile, "Test:$i\n\n");
			fwrite($afile, "Test:$i\n\n");
			foreach($this->questions as $tk=>$tv){
				foreach($tv as $gk=>$gv){
					if($gk=='description') continue;
					if($i>count($gv)-1){
						$qnum=$i-$this->minQuestionPerGroup;
					}else{
						$qnum=$i;
					}
					if($qnum<10)$qnum="0".$qnum;
					$qv=$gv[$qnum];
					fwrite($afile, $tk.$gk.$qnum.": ".$qv['answer']."\n");
					fwrite($qfile, $tk.$gk.$qnum.": ".$qv['question']."\n    A. ".$qv['A']."\n    B. ".$qv['B']."\n    C. ".$qv['C']."\n    D. ".$qv['D']."\n\n");
				}
			}
			fclose($qfile);
			fclose($afile);
		}
	}
	public function makeFlashCardsFlip(){
		require('tcpdf/tcpdf.php');
		$pdf = new TCPDF("P", "in", PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		//$pdf->SetMargins(0.5, 0.5, 0.5);
		$pdf->SetAutoPageBreak(false, 0.5);
		$pdf->SetFont('times', '', 10);
		$pdf->setCellPaddings(0.1, 0.1, 0.1, 0.1);
		$pdf->AddPage();
		$pdf->SetFillColor(255, 255, 255);
		$c=array(
			'width'=>3.75,
			'height'=>2.5,
			'border'=>1,
			'align'=>'J',
			'fill'=>false,
			'x'=>'',
			'y'=>'',
			'reseth'=>true,
			'strech'=>0,
			'ishtml'=>false,
			'autopadding'=>false,
			'maxh'=>2.5,
			'valign'=>'M',
			'fitcell'=>true,
		);
		
		$i=0;
		$answers=array();
		foreach($this->questions as $tk=>$tv){
			foreach($tv as $gk=>$gv){
				if($gk=='description') continue;
				foreach($gv as $qk=>$qv){
					if($qk=='description') continue;
					$i++;
					if($i%2==0){
						$ln=1;
					}else{
						$ln=0;
					}
					$text=$tk.$gk.$qk."\n".$qv['question']."\nA. ".$qv['A']."\nB. ".$qv['B']."\nC. ".$qv['C']."\nD. ".$qv['D']."\n\n";
					$pdf->MultiCell(
						$c['width'],
						$c['height'],
						$text,
						$c['border'],
						$c['align'],
						$c['fill'],
						$ln,
						$c['x'],
						$c['y'],
						$c['reseth'],
						$c['strech'],
						$c['ishtml'],
						$c['autopadding'],
						$c['maxh'],
						$c['valign'],
						$c['fitcell']);
					$answers[]=$tk.$gk.$qk."\n".$qv['answer'].". ".$qv[$qv['answer']]."\n\n";
					if($i%8==0){
						$pdf->AddPage();
						$j=0;
						foreach($answers as $text){
							$j++;
							if($j%2==0){
								$ln=1;
							}else{
								$ln=0;
							}
							$pdf->MultiCell(
								$c['width'],
								$c['height'],
								$text,
								$c['border'],
								$c['align'],
								$c['fill'],
								$ln,
								$c['x'],
								$c['y'],
								$c['reseth'],
								$c['strech'],
								$c['ishtml'],
								$c['autopadding'],
								$c['maxh'],
								$c['valign'],
								$c['fitcell']);
						}
						$answers=array();
						$pdf->AddPage();
					}
				}
			}
		}
		$pdf->Output('hamflashcards.pdf', 'F');
	}
	public function makeFlashCards(){
		require('tcpdf/tcpdf.php');
		$pdf = new TCPDF("P", "in", PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetAutoPageBreak(false, 0.5);
		$pdf->SetFont('times', '', 10);
		$pdf->setCellPaddings(0.1, 0.1, 0.1, 0.1);
		$pdf->AddPage();
		$pdf->SetFillColor(255, 255, 255);
		$c=array(
			'width'=>3.75,
			'height'=>2.5,
			'border'=>1,
			'align'=>'J',
			'fill'=>false,
			'x'=>'',
			'y'=>'',
			'reseth'=>true,
			'strech'=>0,
			'ishtml'=>false,
			'autopadding'=>false,
			'maxh'=>2.5,
			'valign'=>'M',
			'fitcell'=>true,
		);
		
		$i=0;
		foreach($this->questions as $tk=>$tv){
			foreach($tv as $gk=>$gv){
				if($gk=='description') continue;
				foreach($gv as $qk=>$qv){
					if($qk=='description') continue;
					$i++;
					$text=$tk.$gk.$qk."\n".$qv['question']."\nA. ".$qv['A']."\nB. ".$qv['B']."\nC. ".$qv['C']."\nD. ".$qv['D']."\n\n";
					$pdf->MultiCell(
						$c['width'],
						$c['height'],
						$text,
						$c['border'],
						$c['align'],
						$c['fill'],
						0,
						$c['x'],
						$c['y'],
						$c['reseth'],
						$c['strech'],
						$c['ishtml'],
						$c['autopadding'],
						$c['maxh'],
						$c['valign'],
						$c['fitcell']);
					$text=$tk.$gk.$qk."\n".$qv['answer'].". ".$qv[$qv['answer']]."\n\n";
					$pdf->MultiCell(
						$c['width'],
						$c['height'],
						$text,
						$c['border'],
						$c['align'],
						$c['fill'],
						1,
						$c['x'],
						$c['y'],
						$c['reseth'],
						$c['strech'],
						$c['ishtml'],
						$c['autopadding'],
						$c['maxh'],
						$c['valign'],
						$c['fitcell']);
					if($i%4==0)$pdf->AddPage();
				}
			}
		}
		$pdf->Output('hamflashcards.pdf', 'F');
	}	
	public function dump(){
		var_dump($this->questions);
	}
}
$test=new hamtest();
$test->parseInput();
$test->iterativeQuiz();
//$test->questions();
//$test->answers(STDERR,true);
//$test->makeFlashCards();
