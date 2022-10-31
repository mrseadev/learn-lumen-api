<?php

/**
 *
 * PHP version >= 7.0
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */

namespace App\Console\Commands;


use App\Post;

use Exception;
use Illuminate\Console\Command;



/**
 * Class deletePostsCommand
 *
 * @category Console_Command
 * @package  App\Console\Commands
 */
class DeletePostsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = "delete:posts";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Delete all posts";


    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $posts = Post::getPosts();

            if (!$posts) {
                $this->info("No posts exist");
                return;
            }
            foreach ($posts as $post) {
                $post->delete();
            }
            $this->info("All posts have been deleted");
        } catch (Exception $e) {
            $this->error("An error occurred, {$e->getMessage()}");
        }
    }
}
