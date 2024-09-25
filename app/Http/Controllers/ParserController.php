<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class ParserController extends Controller
{

    public function __construct()
    {
        //base folder path
        $this->BASE_URL = base_path("content-pages");

        //parsed files
        $this->PARSED_FILES = [];

        //iterate through all files
        $this->iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path('content-pages'), \RecursiveDirectoryIterator::SKIP_DOTS)
        );
    }

    //get the content of a given folder and return it
    /*path has to be full path, won't be resolved within this method*/
    protected function getFolderContent(string $path)
    {
        //get files
        $files = File::files($path);

        //get directories
        $folders = File::directories($path);

        return [$files, $folders];
    }

    //helper to check if a txt line is an image
    protected function isImage(string $line)
    {
        $extensions = ['.jpg', '.png', '.jpeg', '.gif', '.svg', '.webp'];

        //loop through extensions, and check if line contains it
        foreach ($extensions as $extension) {
            if (str_contains($line, $extension)) {
                return true;
            }
        }
        return false;
    }

    //parse file
    protected function parseFile(SplFileInfo|\SplFileInfo $file)
    {
        //get content
        $fileContent = file_get_contents($file);

        //get extension
        $extension = last(explode('.', $file->getBasename()));

        //parse front matter
        $marker = '---';
        $markerLen = strlen($marker);
        $firstMarkerPos = strpos($fileContent, $marker);
        $secondMarkerPos = strpos($fileContent, $marker, $markerLen);

        //extract front matter
        $frontMatterRaw = substr($fileContent, $firstMarkerPos + $markerLen, $secondMarkerPos - ($firstMarkerPos + $markerLen));

        $frontMatter = Yaml::parse($frontMatterRaw);

        $fileBase = $file->getBasename();

        //remove extension
        $filename = explode('.', $fileBase)[0];

        //check where title is present
        if (!isset($frontMatter['title'])) {
            //if not in file, get title from filename
            /*I won't parse h1 tags, sorry...hate parsing*/
            $fragments = explode('-', $filename);

            //get title everything but date
            $title = (implode(' ', array_slice($fragments, 3)));

            $frontMatter['title'] = $title;
        }

        //get page content
        $pageContent = substr($fileContent, $secondMarkerPos + $markerLen);

        //check extension
        if ($extension === "html") {
            //on html
            $htmlContent = $pageContent;
        } else {
            //on txt

            //replace any type of end of line with \n for consistency
            $pageContent = preg_replace('/\R/', PHP_EOL, $pageContent);

            //extract lines
            $lines = explode(PHP_EOL, $pageContent);

            //remove empty lines
            $lines = array_filter($lines, 'trim');

            $htmlContent = "";

            //turn lines to p tags
            foreach ($lines as $line) {
                if ($this->isImage($line)) {
                    $src = url("images/" . $line);
                    $htmlContent = $htmlContent . "<img src='$src'/";
                } else {
                    $htmlContent = $htmlContent . "<p>" . $line . "</p>";
                }
            }
        }

        //check if matter has tags
        if (isset($frontMatter['tags'])) {
            //create tags array
            $tags = explode(',', $frontMatter['tags']);

            //remove space
            $tags = array_map('trim', $tags);
        }

        //check for draft
        if (isset($frontMatter['draft']) && $frontMatter['draft']) {
            $frontMatter['draft'] = true;
        } else {
            $frontMatter['draft'] = false;
        }

        //parse date from file name
        $filenameFragments = explode('-', $filename);
        if (isset($filenameFragments[0]) && isset($filenameFragments[1]) && isset($filenameFragments[2])) {
            $dateRaw = $filenameFragments[0] . '-' . $filenameFragments[1] . '-' . $filenameFragments[2];
            $date = Carbon::parse($dateRaw)->format('Y-m-d');
        }

        return [
            'title' => $frontMatter['title'] ?? '',
            'summary' => $frontMatter['summary'] ?? '',
            'tags' => $tags ?? [],
            'content' => $htmlContent,
            'filename' => str_replace(' ', '-', $filename),
            'file' => $file->getBasename(),
            'draft' => $frontMatter['draft'],
            'cover' => $frontMatter['cover'] ?? '',
            'date' => $date ?? ''
        ];
    }

    //transform folder paths into real folder name
    protected function extractFolderName($folders)
    {
        $newFolders = [];
        foreach ($folders as $folder) {
            $name = last(preg_split('/[\/\\\\]+/', $folder));
            $newFolders[] = $name;
        }

        return $newFolders;
    }

    //get files and folders based on url
    protected function contentExtractor(string $path)
    {
        //get content from root folder
        [$files, $folders] = $this->getFolderContent($path);

        //extract real folder name from path
        $folders = $this->extractFolderName($folders);

        //send files to parser
        foreach ($files as $file) {
            $parsedFile = $this->parseFile($file);

            //check if is article is draft and check if date is in future date
            /*future articles not included*/
            //check, if date is present (if not, parser returns empty string)

            $dateRaw = $parsedFile['date'];
            $date = Carbon::parse($dateRaw);
            $isFuture = $date->isFuture();

            if (isset($parsedFile['draft']) && $parsedFile['draft'] === true || $isFuture || empty($dateRaw)) {
                continue;
            } else {
                $this->PARSED_FILES[] = $parsedFile;
            }
        }

        return $folders;
    }

    //index page -- listing articles and folders
    public function index()
    {
        $folders = $this->contentExtractor($this->BASE_URL);

        return view('index', ['folders' => $folders, 'files' => $this->PARSED_FILES]);
    }

    //show different folder or file method -- soul of the page
    public function show(string $path)
    {
        //check if received a folder or a file
        /*check if directory exists under current folder*/
        $originalRoute = \Illuminate\Support\Facades\Request::getPathInfo();

        //replace heritages with actual folder name content-pages
        $physicalRoute = str_replace('heritages', 'content-pages', $originalRoute);

        //check if directory exists
        if (is_dir(base_path($physicalRoute))) {
            $folders = $this->contentExtractor(base_path($physicalRoute));
            return view('index', ['folders' => $folders, 'files' => $this->PARSED_FILES]);
        } else if (File::exists(base_path($physicalRoute . '.txt'))) {
            //find file from files
            foreach ($this->iterator as $file) {
                $parsedFile = $this->parseFile($file);
                $fileName = last(explode('/', $physicalRoute));
                $parsedFileNameWithoutExtension = explode('.', $parsedFile['file'])[0];
                //check if file is found
                if ($fileName === $parsedFileNameWithoutExtension) {
                    return view('article', ['file' => $parsedFile]);
                }
            }
        } else if (File::exists(base_path($physicalRoute . '.html'))) {
            //find file from files
            foreach ($this->iterator as $file) {
                $parsedFile = $this->parseFile($file);
                $fileName = last(explode('/', $physicalRoute));
                $parsedFileNameWithoutExtension = explode('.', $parsedFile['file'])[0];
                //check if file is found
                if ($fileName === $parsedFileNameWithoutExtension) {
                    return view('article', ['file' => $parsedFile]);
                }
            }
        } else {
            abort(404);
        }
    }

    //search by keywords
    public function search(Request $request)
    {
        //extract keywords separated by '/'
        $keyword = $request->get('keyword', '');
        $keywords = explode('/', $keyword);
        if (sizeof($keywords) === 0) {
            return redirect()->route('view.index');
        }

        //get files that contain keywords
        $files = [];
        foreach ($this->iterator as $file) {
            $content = file_get_contents($file);
            foreach ($keywords as $keyword) {
                if (str_contains($content, $keyword)) {
                    $files[] = $this->parseFile($file);
                }
            }
        }
        return view('index', ['folders' => [], 'files' => $files]);
    }

    //search by tags
    public function searchByTag(string $tag)
    {
        $files = [];
        foreach ($this->iterator as $file) {
            $parsedFile = $this->parseFile($file);
            if (!empty($parsedFile['tags'])) {
                //if tag is in the article tags, include the file
                if (in_array($tag, $parsedFile['tags'])) {
                    $files[] = $parsedFile;
                }
            }
        }
        return view('index', ['folders' => [], 'files' => $files]);
    }
}
