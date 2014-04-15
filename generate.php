<?php
/**
 * 
 */ 

require __DIR__ . "/vendor/autoload.php";

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateCommand extends Command
{
    public function configure()
    {
        $this->setName('generate');
        $this->addArgument('output-dir', null, 'Output directory', __DIR__ . '/bingo');
        $this->addArgument('input-dir', null, 'Input Directory', __DIR__ . '/images');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $inputDirectory = $input->getArgument('input-dir');
        $outputDirectory = $input->getArgument('output-dir');

        $pageWidth = 850;
        $pageHeight = 1100;


        $imagine = new \Imagine\Imagick\Imagine();


        for ($i = 0; $i <= 25; $i++) {
            $output->writeln(sprintf("<info>Generating bingo matrix %d from images in %s</info>", $i, $inputDirectory));
            $page = new \Imagine\Image\Box($pageWidth, $pageHeight);

            $bingoBoard = $imagine->create($page);

            $x = 0;
            $y = 0;
            $slotCount = 0;

            $randomImages = glob($inputDirectory . "/*");
            shuffle($randomImages);
            foreach ($randomImages as $randomImage) {
                $output->writeln(' <comment>Placing image ' . ($slotCount + 1) . ' ' . $randomImage . '</comment>');

                $slot = $imagine->open($randomImage);
                $currentSlotWidth = $slot->getSize()->getWidth();
                $ratio = $slot->getSize()->getWidth() / $slot->getSize()->getHeight();
//                print_r($ratio);
                $slotWidth = $pageWidth / 5;
                $slotHeight = $pageHeight / 5;

//                if ($ratio > 1) {
//                    $slotHeight /= $ratio;
//                } else {
//                    $slotWidth *= $ratio;
//                }

                $slot->resize(new \Imagine\Image\Box($slotWidth, $slotHeight));

                // Make a white background for the text
                $stripBackground = $imagine->create(new \Imagine\Image\Box($slotWidth, $slotHeight / 10));
                $slot->paste($stripBackground, new \Imagine\Image\Point(0, $slotHeight - $slotHeight / 10));

                // Draw the person's name
                $parts = explode('/', $randomImage);
                $filename = $parts[count($parts) - 1];

                $filename = str_replace([".jpg", ".png"], '', $filename);
//                $filename = str_replace('.', ' ', $filename);
                if (strpos($filename, 'F') === 0) {
                    // strip the F crap
                    $filename = substr($filename, 4);
                }
                $filename = trim($filename);
//                print_r($filename);



                $font = $imagine->font('/usr/share/fonts/truetype/ubuntu-font-family/Ubuntu-B.ttf', strlen($filename) > 20 ? 9 : 10, new \Imagine\Image\Color('#000'));
                $slot->draw()->text($filename, $font, new \Imagine\Image\Point($slotWidth / 26, $slotHeight - ($slotHeight / 11)));

                $slot->draw()->line(new \Imagine\Image\Point(0, 0), new \Imagine\Image\Point(0, $slotHeight), new \Imagine\Image\Color('#000'));
                $slot->draw()->line(new \Imagine\Image\Point(0, $slotHeight), new \Imagine\Image\Point($slotWidth, $slotHeight), new \Imagine\Image\Color('#000'));
                $slot->draw()->line(new \Imagine\Image\Point($slotWidth, $slotHeight), new \Imagine\Image\Point($slotWidth, 0), new \Imagine\Image\Color('#000'));
                $slot->draw()->line(new \Imagine\Image\Point($slotWidth, 0), new \Imagine\Image\Point(0, 0), new \Imagine\Image\Color('#000'));



                $point = new \Imagine\Image\Point($x, $y);
                $bingoBoard->paste($slot, $point);

                $x = $x + $pageWidth / 5;
                if ($x >= $pageWidth) {
                    $x = 0;
                    $y += $pageHeight / 5;
                }

                if ($slotCount++ >= 24) {
                    break;
                }
            }


            $bingoBoard->save($outputDirectory . '/' . 'testimage' . $i . '.jpg');
        }
    }
}

$app = new Application('Bingo Image Generator', '1.0');

$app->add(new GenerateCommand());
$app->run(new ArgvInput());

