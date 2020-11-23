<?php

namespace Readybytes\EnvChecker\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use JSefton\DotEnv\Parser;

class EnvCheckCommand extends Command
{
    /**
     * The ignored files and directories.
     *
     * @var array
     */
    protected $ignore_dir = ['vendor', 'config', 'storage'];
    protected $ignore_file = ['.env'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:check';

    protected $KEY_NOT_FOUND = '<error>N/A</>';
    protected $Value_NOT_FOUND = '-';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check invalid use of env variables';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try
        {
            $basePath = base_path();
            $this->getAllEnv($basePath);
            $this->getAllFiles($basePath);

        }
        catch (\Exception $exception)
        {
            $this->info($exception);
        }
    }


// Compair Env in root directory
// Function to get all Env files name array
    function getAllEnv($basePath){
        $filesAndDirectoriesName = scandir($basePath);

        $filesName = array_filter(
            $filesAndDirectoriesName, function($var) { return preg_match("/\benv\b/i", $var); }
        );

        if(count($filesName) == 0)
            $this->warn("No ENV files found in your project directory : ". $basePath );
        else
            $this->getEnvData($filesName);

    }

// Function to get array of env data and header for table
    function getEnvData($filesName){
        $headers = ['key'];
        foreach($filesName as $fileName) {
            $headers[] = $fileName;
            $envArray[$fileName] = array_filter(
                Parser::envToArray($fileName), function ($key) { return !is_numeric($key); }, ARRAY_FILTER_USE_KEY
            );
        }
        $this->mergeArrayForTableData($envArray,$headers);
    }

// Function to merge all envArray data for the table and print table
    function mergeArrayForTableData($envArray, $headers) {
        $result = array();
        foreach ($envArray as $envName => $array) {
          foreach ($array as $key => $value) {

            if (!array_key_exists($key,$result)) {
                $result[$key]['key'] = $key;
                foreach(array_slice($headers, 1) as $header)
                        $result[$key][$header] = $this->KEY_NOT_FOUND;
            }
            if(!$value)
                $result[$key][$envName] = $this->Value_NOT_FOUND;
            else
                $result[$key][$envName] = $value;
          }
        }
        $data = array_values($result);
        $this->warn("All ENV Files Table");
        $this->table($headers, $data);
      }


// Find env in all files
// Function to get files from all the directories
      function getAllFiles($basePath){
        foreach(File::directories($basePath) as $dir) { // Get all the directories
            if(Str::contains($dir, $this->ignore_dir)) { // Ignore directory
                continue;
            }
            foreach(File::allFiles($dir) as $file) { // Get all the files path in each directory
                if(Str::contains($file, $this->ignore_file)) { // Ignore files
                    continue;
                }
                $filesPath[] = realpath($file);
            }
        }
         $this->processEachFile($filesPath);

    }

// Function to process each file and find env variable in file
    function processEachFile($filesPath){
        foreach($filesPath as $filePath)
            exec('grep -Hniw "env(" '.$filePath , $output);

        if(count($output)){
            $this->warn("Files Which Are Using ENV Variables-");
            print_r( implode( PHP_EOL, $output).PHP_EOL );
        }
        else
            $this->warn("No file Use ENV Variable. All good :)");
    }
}
