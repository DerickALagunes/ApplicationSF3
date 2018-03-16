<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller {

    public function indexAction(Request $request) {
        // replace this example code with whatever you need
        return $this->render('Default/index.html.twig', [
                    'base_dir' => realpath($this->getParameter('kernel.project_dir')) . DIRECTORY_SEPARATOR,
        ]);
    }
    
    public function inicioAction(Request $request) {
        // replace this example code with whatever you need
        return $this->render('Default/inicio.html.twig');
    }

    public function dataBaseAction(KernelInterface $kernel, $connection = 'default') {
        $application = new Application($kernel);
        $application->setAutoExit(false);

        $outputD = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $outputC = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);
        $outputU = new BufferedOutput(OutputInterface::VERBOSITY_NORMAL, true);

        // Drop old database
        $options = array('command' => 'doctrine:database:drop', '--connection' => $connection, '--force' => true);
        $application->run(new ArrayInput($options),$outputD);  
        

        // Make sure we close the original connection because it lost the reference to the database
        $this->getDoctrine()->getManager()->getConnection()->close();

        // Create new database
        $options = array('command' => 'doctrine:database:create', '--connection' => $connection);
        $application->run(new ArrayInput($options),$outputC);


        // Update schema
        $options = array('command' => 'doctrine:schema:update', '--force' => true, '--em' => $connection);
        $application->run(new ArrayInput($options),$outputU);
        
        // return the output
        $converter = new AnsiToHtmlConverter();     
        
        return $this->render('AppBundle:Default:inicio.html.twig', array(
            'consolaD' => $converter->convert($outputD->fetch()),
            'consolaC' => $converter->convert($outputC->fetch()),
            'consolaU' => $converter->convert($outputU->fetch())
        ));
    }

}
