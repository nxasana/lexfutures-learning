<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use AppBundle\Service\Util;
use AppBundle\Form\rulJurisdictionType;

use AppBundle\Entity\Jurisdiction;
use AppBundle\Entity\File;
use AppBundle\Entity\Courtcase;
use AppBundle\Entity\Judge;
use AppBundle\Entity\User;
use AppBundle\Entity\Note;
use AppBundle\Entity\Prediction;


class ReportsController extends Controller
{
        
    /**
     * @Route("/admin/reports/judges/accuracy", name="lexfutures_admin_report_judges")
     */
    public function judgesAccuracyRawAction(Request $request)
    {
        
        $name = $this->getUser()->getFirstName()." ".$this->getUser()->getLastName();
        
        $util = $this->get(Util::class);
        
        $stats = $util->getJudgesAccuracy($this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById(3));
        
        foreach ($stats as $stat) {
            
            echo $stat["judge"]->getJusticeTitle()->getName() ." ". $stat["judge"]->getName()."|".$stat["correct"]."|".$stat["incorrect"]."|".$stat["total"];
            echo "<br/>";
            
        }
        
        die();
        
    }
    
    /**
     * @Route("/admin/reports/judges/accuracy/excel", name="lexfutures_admin_report_judges_excel")
     */
    public function judgesAccuracyExcelAction(Request $request)
    {
        
        $util = $this->get(Util::class);
        
        $stats = $util->getJudgesAccuracy($this->getDoctrine()->getRepository('AppBundle:Jurisdiction')->findOneById(3));
        
        $writefolders = $this->container->getParameter('data');
            
        $folder = $this->get('kernel')->getRootDir().$writefolders["reports"];
        
        $filename = uniqid()."_JudgesAccuracy.xlsx";
        
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        
//      \PHPExcel_Shared_Font::setTrueTypeFontPath('C:/Windows/Fonts/');
        
        //\PHPExcel_Shared_Font::setAutoSizeMethod(\PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT);
        
        $report_requester = $this->getUser()->getFirstName()." ".$report_requester = $this->getUser()->getLastName();
        
        $report_title = "Judges Accuracy";
        
        $report_description = "List of Judges and the accuracy of user predictions";
        
        $phpExcelObject->getProperties()->setCreator($report_requester)
                ->setLastModifiedBy($report_requester)
                ->setTitle($report_title)
                ->setSubject($report_title)
                ->setDescription($report_description)
                ->setCategory("Report");
        
        $phpExcelObject->getActiveSheet()->getStyle('A1:E1')->applyFromArray(
                array('fill' => array(
                        'type'	=> \PHPExcel_Style_Fill::FILL_SOLID,
                        'color'     => array('argb' => 'ff4f81bd')
                    ),'font' => array('bold' => true,)
                )
            );
        
        $phpExcelObject->getActiveSheet()->getStyle('A1:E1')->getFont()->getColor()->setARGB(\PHPExcel_Style_Color::COLOR_WHITE); //Setting font colour for headings
        $phpExcelObject->getActiveSheet()->getStyle('D1:E1000')->getAlignment()->setWrapText(true); //Set textwrap on col.4
        
        $phpExcelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Judge')
            ->setCellValue('B1', 'Correct Predictions')
            ->setCellValue('C1', 'Incorrect Predictions')
            ->setCellValue('D1', 'Total')->setCellValue('E1', 'Accuracy');
        
        $row=2;
                
        foreach ($stats as $stat) {
            
            if ($stat["total"] !== 0) {
                
                $accuracy = round(($stat["correct"] / $stat["total"]) * 100, 2);
                
            } else {
                
                $accuracy = 0;
                
            }
            
            $phpExcelObject->setActiveSheetIndex(0)
                ->setCellValue('A'.$row, $stat["judge"]->getJusticeTitle()->getName() ." ". $stat["judge"]->getName())
                ->setCellValue('B'.$row, $stat["correct"])
                ->setCellValue('C'.$row, $stat["incorrect"])
                ->setCellValue('D'.$row, $stat["total"])
                ->setCellValue('E'.$row, $accuracy);

            $row += 1;
        }
        
        //CONFIGURABLE - Set alternating fill colour for data rows
        for ($i=2; $i<=$row; $i++) {
            if ($i % 2 == 0) {
                $phpExcelObject->getActiveSheet()->getStyle('A'.$i.':E'.$row)->applyFromArray(array('fill' => array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'aae3effe'))));
            } else {
                $phpExcelObject->getActiveSheet()->getStyle('A'.$i.':E'.$row)->applyFromArray(array('fill' => array('type' => \PHPExcel_Style_Fill::FILL_SOLID, 'color' => array('argb' => 'ffd8e7fa'))));
            }
        }
        
        //CONFIGURABLE - Set vertical alignment for data cells
        $phpExcelObject->getActiveSheet()->getStyle('A1:E'.$row)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_TOP);

        //Attempt to resize the columns
        /*
        foreach(range('A','E') as $columnID) {
            $phpExcelObject->getActiveSheet()->getColumnDimension($columnID)->setAutoSize(true);
        }
        */
        $objWriter = \PHPExcel_IOFactory::createWriter($phpExcelObject, 'Excel2007');
        
        $objWriter->save($folder.$filename);
        
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
        
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment;filename='.$filename);
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');

        return $response; 
        
        echo "bung";
        die();
        
    }
}
