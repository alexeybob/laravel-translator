<?php

namespace Translator\Console;

use Illuminate\Console\Command;
use Translator\Services\TranslatorService;
use Illuminate\Http\Exceptions\HttpResponseException;


/**
 * A command that parses templates to extract translation messages and adds them
 * into the translation files.
 *
 * @author Alexey Bob <alexey.bob@gmail.com>
 *
 * @final
 * @example php artisan translation:diff en
 */
class TranslationDiffCommand extends Command
{
    private $messages = [];
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translation:diff
        
                                {locale : The locale}
                                
                                {path=views : Directory where to load the messages, defaults to views folder}
                                                                
                                {--prefix=__,@lang,trans_choice,@choice,__ab,trans_choice_ab : Override the default prefix. Default "__,@lang,trans_choice,@choice,__ab,trans_choice_ab"}
                           ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Difference between translation files and source code messages.';

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
        // validate locale
        $validator = \Validator::make(['locale' => $this->argument('locale')], ['locale' => 'required|locale']);
        if ($validator->fails()) {
            $this->error('Locale Errors:');
            foreach ($validator->errors()->getMessages() as $key => $errors) {
                foreach ($errors as $error) {
                    $this->error('  • ' . $key . ': ' .  $error);
                }
            }
            
            return 1;
        }
        
        // Define Root Paths
        $root_path = resource_path($this->argument('path'));
        if (!is_dir($root_path)) {
            
            $this->error(sprintf('"%s" is neither a file nor a directory of resources.', $this->argument('path')));
            
            return 1;
        }
        
        $this->alert('Translation Messages Extractor');
        $this->translator = new TranslatorService($this->argument('locale'), $root_path, $this->option('prefix'));

        $this->comment('Parsing templates...');
        $this->line('');
        $this->comment('Loading translation files...');
        $this->line('');
        
        $this->newMessages = $this->translator->getNewMessages(true);

        // Exit if no new messages found.
        if (!count($this->newMessages)) {
            $this->info('No new translation messages were found.');
        } else {
            $this->comment(sprintf('New messages found (%d message%s) for the "<info>%s</info>" locale', count($this->newMessages), count($this->newMessages) > 1 ? 's' : '', $this->argument('locale')));
            
            $this->table(array('Messages'), $this->newMessages);
        }

        return ;
    }
}