<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;

/**
 * Features context.
 */
class ModuleTransformer extends BehatContext
{
	protected $app;
	
    public function __construct(array $parameters, TestBootstrap $app)
    {
    	$this->app = $app;  
    }
	
	/**
     * Albums
	 * 
	 * @Transform /^table:title,artist$/
     */
    public function castAlbumsTable(TableNode $albumsTable)
    {
    	
        $albums = array();
        foreach ($albumsTable->getHash() as $row) {
            
            $album = new Album\Entity\Album;
			$album->title = $row['title'];
			$album->artist = $row['artist'];
			$this->app->em->persist($album);
			$this->app->em->flush();
			
            $albums[] = $album;
        }

        return $albums;
    }
	
	/**
     * @Given /^the following albums exist:$/
     */
    public function theFollowingAlbumsExist(array $albums)
    {
        foreach($albums as $album){
        	$retrived_album = $this->app->em->getRepository("Album\Entity\Album")->findOneBy(array('title' => $album->title));
			
			$retrived_album_title = $retrived_album->title;
			$actual_album_title   = $album->title;
			
			assertEquals($retrived_album_title, $actual_album_title, "Retrived album wit title \"{$retrived_album_title}\" is not equal to actual album with title \"{$actual_album_title}\"");
        }
    }
}