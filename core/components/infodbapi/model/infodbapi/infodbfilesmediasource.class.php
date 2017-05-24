<?php

require_once MODX_CORE_PATH . 'model/modx/sources/modmediasource.class.php';
/**
 * Implements an OpenStack Object Storage (Swift) media source, allowing basic manipulation, uploading and URL-retrieval of resources
 * in a specified Swift container.
 * 
 * @package modx
 * @subpackage sources
 */
class infoDBfilesMediaSource extends modMediaSource implements modMediaSourceInterface {
    /** @var $connection */
    public $connection;
    /** @var $container */
    public $container;

    private $url='';
    private $api_key='';
    private $root_id=0;
    private $client;
    /**
     * Override the constructor to always force Swift sources to not be streams.
     *
     * {@inheritDoc}
     *
     * @param xPDO $xpdo
     */
    public function __construct(xPDO & $xpdo) {
        parent::__construct($xpdo);
        $this->set('is_stream',false);
    }

    /**
     * Initializes Swift media class, connect and get container
     * @return boolean
     */
    public function initialize() {
        parent::initialize();
        $properties = $this->getPropertyList();
        $this->url=$this->xpdo->getOption('infodbfiles.url',$properties,'');
        $this->api_key=$this->xpdo->getOption('infodbfiles.api_key',$properties,'');
        $this->root_id=$this->xpdo->getOption('infodbfiles.root_id',$properties,'');
     //   $this->xpdo->log(3, "initialize-->0");
        include_once dirname(__FILE__).'/infoDBfilesClient.php';
       // $this->xpdo->log(3, "initialize-->1");
        $this->client=new infoDBfilesClient($this->url,$this->api_key);
      //  $this->xpdo->log(3, "initialize-->2");
        return true;
    }

    /**
     * Get the name of this source type
     * @return string
     */
    public function getTypeName() {
        $this->xpdo->lexicon->load('infodbfiles:default');
        return $this->xpdo->lexicon('source_type.infodbfiles');
    }
    /**
     * Get the description of this source type
     * @return string
     */
    public function getTypeDescription() {
        $this->xpdo->lexicon->load('infodbfiles:default');
        return $this->xpdo->lexicon('source_type.infodbfiles_desc');
    }


    /**
     * Authentificate in Swift
     * @return CF_Connection
     */
    /*
    public function connect() {
        if (empty($this->connection)) {
            try {
                $properties = $this->getPropertyList();
                
                $username = $this->xpdo->getOption('username',$properties,'');
                $api_key = $this->xpdo->getOption('api_key',$properties,'');
                $authentication_service = $this->xpdo->getOption('authentication_service',$properties,'');
                
                $auth = new CF_Authentication($username, $api_key,NULL,$authentication_service);
                $auth->authenticate();
                $this->connection = new CF_Connection($auth);

            } catch (Exception $e) {
                $this->xpdo->log(modX::LOG_LEVEL_ERROR,'[SwiftMediaSource] Could not authentificate: '.$e->getMessage());
            }
        }
        return $this->connection;
    }

    /**
     * Set the containder for the connection to Swift
     * @param string $container
     * @return void
     */
    public function setContainer($container) {
        $this->xpdo->log(3, "setContainer-->".$container);
        $this->container = $this->connection->get_container($container);
    }

    /**
     * Get a list of objects from within a bucket
     * @param string $dir
     * @return array
     */
    public function getSwiftObjectList($dir) {
        $path = !empty($dir)?ltrim($dir,'/'):'';

        $objects = $this->container->get_objects(0,NULL,NULL,$path,'/');
        return $objects;
    }


    /**
     * Tells if a file is a binary file or not
     * @param CF_Object $obj
     * @return boolean
     */
    public function isBinary($obj) {
        if ($obj instanceof CF_Object) {
            return (stripos($obj->content_type, 'text') !== 0);
        }
        return false;

    }

    /**
     * @param string $path
     * @return array
     */
    public function getContainerList($path) {
        if ($path=='/'){
            $query='{"query":{"locales":["ru_RU"],"sortField":"node_type","fields":["id","name","description","ext","file_name","size","mtime","node_type","parent_id"],"filter":[["parent_id",'.$this->root_id.']]}}';
        }else{
            $query='{"query":{"locales":["ru_RU"],"sortField":"node_type","fields":["id","name","description","ext","file_name","size","mtime","node_type","parent_id"],"filter":[["parent_id",'.$path.']]}}';
        }
     //   $list = $this->getSwiftObjectList($path);
      //  $this->xpdo->log(3, "getContainerList-->".$path);
        $list=$this->client->make_query($query);
      //  $this->xpdo->log(3, "getContainerList-->".json_encode($list));
      //  $useMultiByte = $this->ctx->getOption('use_multibyte', false);
      //  $encoding = $this->ctx->getOption('modx_charset', 'UTF-8');

        $directories = array();
        $files = array();
        foreach ($list as $idx => $obj) {
         //   $currentPath = $obj->name;
            //$fileName = basename($obj->name);
          //  $isDir = strtolower($obj->content_type)=='application/directory';
/*
 * "id":"assets\/components\/activedirectory\/",
 * "text":"activedirectory",
 * "cls":"folder pchmod pcreate premove pupdate pupload pcreate",
 * "iconCls":"icon icon-folder",
 * "type":"dir",
 * "leaf":false,
 * "path":"\/var\/www\/int\/html\/assets\/components\/activedirectory",
 * "pathRelative":"assets\/components\/activedirectory",
 * "perms":"0755",
 * "menu":{
 * "items":[{
 * "text":"\u0421\u043e\u0437\u0434\u0430\u0442\u044c \u043a\u0430\u0442\u0430\u043b\u043e\u0433 \u0437\u0434\u0435\u0441\u044c",
 * "handler":"this.createDirectory"},{
 * "text":"\u0418\u0437\u043c\u0435\u043d\u0438\u0442\u044c \u043f\u0440\u0430\u0432\u0430 \u043d\u0430 \u043a\u0430\u0442\u0430\u043b\u043e\u0433",
 * "handler":"this.chmodDirectory"},{
 * "text":"\u041f\u0435\u0440\u0435\u0438\u043c\u0435\u043d\u043e\u0432\u0430\u0442\u044c",
 * "handler":"this.renameDirectory"},{
 * "text":"\u041e\u0431\u043d\u043e\u0432\u0438\u0442\u044c",
 * "handler":"this.refreshActiveNode"},
 * "-",{"
 * text":"\u0417\u0430\u0433\u0440\u0443\u0437\u0438\u0442\u044c \u0444\u0430\u0439\u043b\u044b",
 * "handler":"this.uploadFiles"},{
 * "text":"\u0421\u043e\u0437\u0434\u0430\u0442\u044c \u0444\u0430\u0439\u043b",
 * "handler":"this.createFile"},{
 * "text":"\u0411\u044b\u0441\u0442\u0440\u043e \u0441\u043e\u0437\u0434\u0430\u0442\u044c \u0444\u0430\u0439\u043b",
 * "handler":"this.quickCreateFile"},
 * "-",{
 * "text":"\u0423\u0434\u0430\u043b\u0438\u0442\u044c",
 * "handler":"this.removeDirectory"}]}}
 */
     //       $extension = pathinfo($fileName,PATHINFO_EXTENSION);
     //       $extension = $useMultiByte ? mb_strtolower($extension,$encoding) : strtolower($extension);

            if ($obj->node_type<2) {
                $directories[$obj->id] = array(
                    'id' => $obj->id,
                    'text' => $obj->name,
                    'cls' => 'folder pchmod pcreate premove pupdate pupload pcreate',
                    'iconCls'=>'icon icon-folder',
                    'type' => 'dir',
                    'leaf' => false,
                    'path' => $obj->id,
                    'pathRelative' => $obj->id,
                    'perms' => '0755',
                );
                $directories[$obj->id]['menu'] = array('items' => $this->getListContextMenu($obj->id,$isDir,$directories[$obj->id], false));
            } else {
                $files[$obj->id] = array(
                    'id' => $obj->id,
                    'text' => $obj->name,
                    'cls'=>'premove pupdate',
                    'iconCls' => 'icon-'.$obj->ext,
                    'type' => 'file',
                    'leaf' => true,
                    'directory' => $obj->parent_id,
                    'url' => rtrim($properties['url'],'/').'/'.$currentPath,
                    'file' => $obj->name,
                );
                $isBinary = $this->isBinary($obj);
                $files[$obj->id]['menu'] = array('items' => $this->getListContextMenu($obj->id, $isDir,$files[$obj->id], $isBinary));
            }
        }

        $ls = array();
        /* now sort files/directories */
        ksort($directories);
        foreach ($directories as $dir) {
            $ls[] = $dir;
        }
        ksort($files);
        foreach ($files as $file) {
            $ls[] = $file;
        }

        return $ls;
    }

    /**
     * Get the context menu for when viewing the source as a tree
     * 
     * @param string $file
     * @param boolean $isDir
     * @param array $fileArray
     * @return array
     */
    public function getListContextMenu($file,$isDir,array $fileArray, $isBinary) {
        $menu = array();
        if (!$isDir) { /* files */
            if ($this->hasPermission('file_update')) {
                if (!$isBinary) {
                    $menu[] = array(
                        'text' => $this->xpdo->lexicon('file_edit'),
                        'handler' => 'this.editFile',
                    );
                    $menu[] = array(
                        'text' => $this->xpdo->lexicon('quick_update_file'),
                        'handler' => 'this.quickUpdateFile',
                    );
                }
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('rename'),
                    'handler' => 'this.renameFile',
                );
            }
            if ($this->hasPermission('file_view')) {
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('file_download'),
                    'handler' => 'this.downloadFile',
                );
            }
            if ($this->hasPermission('file_remove')) {
                if (!empty($menu)) $menu[] = '-';
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('file_remove'),
                    'handler' => 'this.removeFile',
                );
            }
        } else { /* directories */
            if ($this->hasPermission('directory_create')) {
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('file_folder_create_here'),
                    'handler' => 'this.createDirectory',
                );
            }
            /*if ($this->hasPermission('directory_update')) {
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('rename'),
                    'handler' => 'this.renameDirectory',
                );
            }*/
            $menu[] = array(
                'text' => $this->xpdo->lexicon('directory_refresh'),
                'handler' => 'this.refreshActiveNode',
            );
            if ($this->hasPermission('file_upload')) {
                $menu[] = '-';
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('upload_files'),
                    'handler' => 'this.uploadFiles',
                );
            }
            if ($this->hasPermission('file_create')) {
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('file_create'),
                    'handler' => 'this.createFile',
                );
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('quick_create_file'),
                    'handler' => 'this.quickCreateFile',
                );
            }
            if ($this->hasPermission('directory_remove')) {
                $menu[] = '-';
                $menu[] = array(
                    'text' => $this->xpdo->lexicon('file_folder_remove'),
                    'handler' => 'this.removeDirectory',
                );
            }
        }
        return $menu;
    }

    /**
     * Get all files in the directory and prepare thumbnail views
     * 
     * @param string $path
     * @return array
     */
    public function getObjectsInContainer($path) {
        $list = $this->getSwiftObjectList($path);
        $properties = $this->getPropertyList();

        $modAuth = $this->xpdo->user->getUserToken($this->xpdo->context->get('key'));

        /* get default settings */
        $use_multibyte = $this->ctx->getOption('use_multibyte', false);
        $encoding = $this->ctx->getOption('modx_charset', 'UTF-8');
        $containerUrl = rtrim($properties['url'],'/').'/';
        $allowedFileTypes = $this->getOption('allowedFileTypes',$this->properties,'');
        $allowedFileTypes = !empty($allowedFileTypes) && is_string($allowedFileTypes) ? explode(',',$allowedFileTypes) : $allowedFileTypes;
        $imageExtensions = $this->getOption('imageExtensions',$this->properties,'jpg,jpeg,png,gif');
        $imageExtensions = explode(',',$imageExtensions);
        $thumbnailType = $this->getOption('thumbnailType',$this->properties,'png');
        $thumbnailQuality = $this->getOption('thumbnailQuality',$this->properties,90);
        $skipFiles = $this->getOption('skipFiles',$this->properties,'.svn,.git,_notes,.DS_Store');
        $skipFiles = explode(',',$skipFiles);
        $skipFiles[] = '.';
        $skipFiles[] = '..';

        /* iterate */
        $files = array();
        foreach ($list as $obj) {
            $objectUrl = $containerUrl.trim($obj->name,'/');
            $baseName = basename($obj->name);
            $isDir = strtolower($obj->content_type)=='application/directory';
            if (in_array($obj->name,$skipFiles)) continue;

            if (!$isDir) {
                $fileArray = array(
                    'id' => $obj->name,
                    'name' => $baseName,
                    'url' => $objectUrl,
                    'relativeUrl' => $objectUrl,
                    'fullRelativeUrl' => $objectUrl,
                    'pathname' => $objectUrl,
                    'size' => $obj->content_length,
                    'leaf' => true,
                    'menu' => array(
                        array('text' => $this->xpdo->lexicon('file_remove'),'handler' => 'this.removeFile'),
                    ),
                );

                $fileArray['ext'] = pathinfo($baseName,PATHINFO_EXTENSION);
                $fileArray['ext'] = $use_multibyte ? mb_strtolower($fileArray['ext'],$encoding) : strtolower($fileArray['ext']);
                $fileArray['cls'] = 'icon-'.$fileArray['ext'];

                if (!empty($allowedFileTypes) && !in_array($fileArray['ext'],$allowedFileTypes)) continue;

                /* get thumbnail */
                if (in_array($fileArray['ext'],$imageExtensions)) {
                    $imageWidth = $this->ctx->getOption('filemanager_image_width', 400);
                    $imageHeight = $this->ctx->getOption('filemanager_image_height', 300);
                    $thumbHeight = $this->ctx->getOption('filemanager_thumb_height', 60);
                    $thumbWidth = $this->ctx->getOption('filemanager_thumb_width', 80);

                    $size = @getimagesize($objectUrl);
                    if (is_array($size)) {
                        $imageWidth = $size[0] > 800 ? 800 : $size[0];
                        $imageHeight = $size[1] > 600 ? 600 : $size[1];
                    }

                    /* ensure max h/w */
                    if ($thumbWidth > $imageWidth) $thumbWidth = $imageWidth;
                    if ($thumbHeight > $imageHeight) $thumbHeight = $imageHeight;

                    /* generate thumb/image URLs */
                    $thumbQuery = http_build_query(array(
                        'src' => $obj->name,
                        'w' => $thumbWidth,
                        'h' => $thumbHeight,
                        'f' => $thumbnailType,
                        'q' => $thumbnailQuality,
                        'HTTP_MODAUTH' => $modAuth,
                        'wctx' => $this->ctx->get('key'),
                        'source' => $this->get('id'),
                    ));
                    $imageQuery = http_build_query(array(
                        'src' => $obj->name,
                        'w' => $imageWidth,
                        'h' => $imageHeight,
                        'HTTP_MODAUTH' => $modAuth,
                        'f' => $thumbnailType,
                        'q' => $thumbnailQuality,
                        'wctx' => $this->ctx->get('key'),
                        'source' => $this->get('id'),
                    ));
                    $fileArray['thumb'] = $this->ctx->getOption('connectors_url', MODX_CONNECTORS_URL).'system/phpthumb.php?'.urldecode($thumbQuery);
                    $fileArray['image'] = $this->ctx->getOption('connectors_url', MODX_CONNECTORS_URL).'system/phpthumb.php?'.urldecode($imageQuery);

                } else {
                    $fileArray['thumb'] = $this->ctx->getOption('manager_url', MODX_MANAGER_URL).'templates/default/images/restyle/nopreview.jpg';
                    $fileArray['thumbWidth'] = $this->ctx->getOption('filemanager_thumb_width', 80);
                    $fileArray['thumbHeight'] = $this->ctx->getOption('filemanager_thumb_height', 60);
                }
                $files[] = $fileArray;
            }
        }
        return $files;
    }

    /**
     * Create a Container
     *
     * @param string $name
     * @param string $parentContainer
     * @return boolean
     */
    public function createContainer($name,$parentContainer) {
        $parentContainer = trim($parentContainer,'/') . '/';
        if ($parentContainer == '/' || $parentContainer == '.') $parentContainer = '';

        $newPath = $parentContainer.trim($name,'/');
        $obj = new CF_Object($this->container,$newPath,false);
        /* check to see if folder already exists */
        if (!empty($obj->content_type)) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_ae').': '.$newPath);
            return false;
        }

        /* create path */
        $obj->content_type = 'application/directory';
        if (!$obj->write('.')) {
            $this->addError('name',$this->xpdo->lexicon('file_folder_err_create').$newPath);
            return false;
        }

        $this->xpdo->logManagerAction('directory_create','',$newPath);
        return true;
    }

    /**
     * Remove an empty folder
     *
     * @param $path
     * @return boolean
     */
    public function removeContainer($path) {
        try {
            $obj = new CF_Object($this->container,trim($path,'/'),true);
    
        }
        catch (NoSuchObjectException $e) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_ns').': '.$path);
            return false;
        }

       /* remove object */
        $deleted = $this->container->delete_object($obj);

        /* log manager action */
        $this->xpdo->logManagerAction('directory_remove','',$path);
        return $deleted;
    }

    /**
     * Rename a container
     *
     * @param string $oldPath
     * @param string $newName
     * @return boolean
     */
    public function renameContainer($oldPath,$newName) {
        return false; //TODO: Need manual move all files in container??
        try {
            $obj = new CF_Object($this->container,$oldPath,true);
        }
        catch (NoSuchObjectException $e) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_ns').': '.$oldPath);
            return false;
        }
    
        $dir = dirname($oldPath);
        $newPath = ($dir != '.' ? $dir.'/' : '').$newName;

        $moved = $this->container->move_object_to($oldPath, $this->container, $newPath);
        if (!$moved) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_rename').': '.$oldPath);
            return false;
        }

        $this->xpdo->logManagerAction('directory_rename','',$oldPath);
        return $moved;

    }

    /**
     * Delete a file
     * 
     * @param string $objectPath
     * @return boolean
     */
    public function removeObject($objectPath) {
        try {
            $obj = new CF_Object($this->container,$objectPath,true);
    
        }
        catch (NoSuchObjectException $e) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_ns').': '.$objectPath);
            return false;
        }

       /* remove object */
        $deleted = $this->container->delete_object($objectPath);

        /* log manager action */
        $this->xpdo->logManagerAction('file_remove','',$objectPath);
        return $deleted;
    }

    /**
     * Rename/move a file
     * 
     * @param string $oldPath
     * @param string $newName
     * @return bool
     */
    public function renameObject($oldPath,$newName) {
        try {
            $obj = new CF_Object($this->container,$oldPath,true);
        }
        catch (NoSuchObjectException $e) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_ns').': '.$oldPath);
            return false;
        }
    
        $dir = dirname($oldPath);
        $newPath = ($dir != '.' ? $dir.'/' : '').$newName;

        $moved = $this->container->move_object_to($oldPath, $this->container, $newPath);
        if (!$moved) {
            $this->addError('file',$this->xpdo->lexicon('file_folder_err_rename').': '.$oldPath);
            return false;
        }

        $this->xpdo->logManagerAction('file_rename','',$oldPath);
        return $moved;

    }


    /**
     * Update the contents of a specific object
     *
     * @param string $objectPath
     * @param string $content
     * @return boolean|string
     */
    public function updateObject($objectPath,$content) {

        try {
            $obj = new CF_Object($this->container,ltrim($objectPath,'/'),true);
    
        }
        catch (NoSuchObjectException $e) {
            $this->addError('file',$this->xpdo->lexicon('file_err_ns').': '.$objectPath);
            return false;
        }

        /* update file */
        $obj->write($content);

        $this->xpdo->logManagerAction('file_create','',$filePath);

        return rawurlencode($objectPath);

    }

    /**
     * Create an object from a path
     *
     * @param string $objectPath
     * @param string $name
     * @param string $content
     * @return boolean|string
     */
    public function createObject($objectPath,$name,$content) {
        $objectPath = trim($objectPath,'/') . '/';
        if ($objectPath == '/' || $objectPath == '.') $objectPath = '';

        $filePath = $objectPath.trim($name,'/');

        $obj = new CF_Object($this->container,$filePath,false);

        /* create file */
        $ext = pathinfo($name, PATHINFO_EXTENSION);
        $obj->content_type = $this->getContentType($ext);
        if (!$obj->write($content)) {
            $this->addError('name',$this->xpdo->lexicon('file_err_nf').': '.$filePath);
            return false;
        }


        $this->xpdo->logManagerAction('file_create','',$filePath);

        return rawurlencode($filePath);
    }


    /**
     * Upload files to Swift
     * 
     * @param string $container
     * @param array $objects
     * @return bool
     */
    public function uploadObjectsToContainer($container,array $objects = array()) {
        $container = trim($container,'/') . '/';
        if ($container == '/' || $container == '.') $container = '';

        $allowedFileTypes = explode(',',$this->xpdo->getOption('upload_files',null,''));
        $allowedFileTypes = array_merge(explode(',',$this->xpdo->getOption('upload_images')),explode(',',$this->xpdo->getOption('upload_media')),explode(',',$this->xpdo->getOption('upload_flash')),$allowedFileTypes);
        $allowedFileTypes = array_unique($allowedFileTypes);
        $maxFileSize = $this->xpdo->getOption('upload_maxsize',null,1048576);

        /* loop through each file and upload */
        foreach ($objects as $file) {
            if ($file['error'] != 0) continue;
            if (empty($file['name'])) continue;
            $ext = @pathinfo($file['name'],PATHINFO_EXTENSION);
            $ext = strtolower($ext);

            if (empty($ext) || !in_array($ext,$allowedFileTypes)) {
                $this->addError('path',$this->xpdo->lexicon('file_err_ext_not_allowed',array(
                    'ext' => $ext,
                )));
                continue;
            }
            $size = @filesize($file['tmp_name']);

            if ($size > $maxFileSize) {
                $this->addError('path',$this->xpdo->lexicon('file_err_too_large',array(
                    'size' => $size,
                    'allowed' => $maxFileSize,
                )));
                continue;
            }

            $newPath = $container.$file['name'];


            $obj = new CF_Object($this->container, $newPath);
            $obj->content_type = $this->getContentType($ext);
            $obj->content_length = $size;
            $uploaded = $obj->load_from_filename($file['tmp_name']);

            if (!$uploaded) {
                $this->addError('path',$this->xpdo->lexicon('file_err_upload'));
            }
        }

        /* invoke event */
        $this->xpdo->invokeEvent('OnFileManagerUpload',array(
            'files' => &$objects,
            'directory' => $container,
            'source' => &$this,
        ));

        $this->xpdo->logManagerAction('file_upload','',$container);

        return true;
    }

    /**
     * Get the content type of the file based on extension
     * @param string $ext
     * @return string
     */
    protected function getContentType($ext) {
        $contentType = 'application/octet-stream';
        $mimeTypes = array(
            '323' => 'text/h323',
            'acx' => 'application/internet-property-stream',
            'ai' => 'application/postscript',
            'aif' => 'audio/x-aiff',
            'aifc' => 'audio/x-aiff',
            'aiff' => 'audio/x-aiff',
            'asf' => 'video/x-ms-asf',
            'asr' => 'video/x-ms-asf',
            'asx' => 'video/x-ms-asf',
            'au' => 'audio/basic',
            'avi' => 'video/x-msvideo',
            'axs' => 'application/olescript',
            'bas' => 'text/plain',
            'bcpio' => 'application/x-bcpio',
            'bin' => 'application/octet-stream',
            'bmp' => 'image/bmp',
            'c' => 'text/plain',
            'cat' => 'application/vnd.ms-pkiseccat',
            'cdf' => 'application/x-cdf',
            'cer' => 'application/x-x509-ca-cert',
            'class' => 'application/octet-stream',
            'clp' => 'application/x-msclip',
            'cmx' => 'image/x-cmx',
            'cod' => 'image/cis-cod',
            'cpio' => 'application/x-cpio',
            'crd' => 'application/x-mscardfile',
            'crl' => 'application/pkix-crl',
            'crt' => 'application/x-x509-ca-cert',
            'csh' => 'application/x-csh',
            'css' => 'text/css',
            'dcr' => 'application/x-director',
            'der' => 'application/x-x509-ca-cert',
            'dir' => 'application/x-director',
            'dll' => 'application/x-msdownload',
            'dms' => 'application/octet-stream',
            'doc' => 'application/msword',
            'dot' => 'application/msword',
            'dvi' => 'application/x-dvi',
            'dxr' => 'application/x-director',
            'eps' => 'application/postscript',
            'etx' => 'text/x-setext',
            'evy' => 'application/envoy',
            'exe' => 'application/octet-stream',
            'fif' => 'application/fractals',
            'flr' => 'x-world/x-vrml',
            'gif' => 'image/gif',
            'gtar' => 'application/x-gtar',
            'gz' => 'application/x-gzip',
            'h' => 'text/plain',
            'hdf' => 'application/x-hdf',
            'hlp' => 'application/winhlp',
            'hqx' => 'application/mac-binhex40',
            'hta' => 'application/hta',
            'htc' => 'text/x-component',
            'htm' => 'text/html',
            'html' => 'text/html',
            'htt' => 'text/webviewhtml',
            'ico' => 'image/x-icon',
            'ief' => 'image/ief',
            'iii' => 'application/x-iphone',
            'ins' => 'application/x-internet-signup',
            'isp' => 'application/x-internet-signup',
            'jfif' => 'image/pipeg',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'js' => 'application/x-javascript',
            'latex' => 'application/x-latex',
            'lha' => 'application/octet-stream',
            'lsf' => 'video/x-la-asf',
            'lsx' => 'video/x-la-asf',
            'lzh' => 'application/octet-stream',
            'm13' => 'application/x-msmediaview',
            'm14' => 'application/x-msmediaview',
            'm3u' => 'audio/x-mpegurl',
            'man' => 'application/x-troff-man',
            'mdb' => 'application/x-msaccess',
            'me' => 'application/x-troff-me',
            'mht' => 'message/rfc822',
            'mhtml' => 'message/rfc822',
            'mid' => 'audio/mid',
            'mny' => 'application/x-msmoney',
            'mov' => 'video/quicktime',
            'movie' => 'video/x-sgi-movie',
            'mp2' => 'video/mpeg',
            'mp3' => 'audio/mpeg',
            'mpa' => 'video/mpeg',
            'mpe' => 'video/mpeg',
            'mpeg' => 'video/mpeg',
            'mpg' => 'video/mpeg',
            'mpp' => 'application/vnd.ms-project',
            'mpv2' => 'video/mpeg',
            'ms' => 'application/x-troff-ms',
            'mvb' => 'application/x-msmediaview',
            'nws' => 'message/rfc822',
            'oda' => 'application/oda',
            'p10' => 'application/pkcs10',
            'p12' => 'application/x-pkcs12',
            'p7b' => 'application/x-pkcs7-certificates',
            'p7c' => 'application/x-pkcs7-mime',
            'p7m' => 'application/x-pkcs7-mime',
            'p7r' => 'application/x-pkcs7-certreqresp',
            'p7s' => 'application/x-pkcs7-signature',
            'pbm' => 'image/x-portable-bitmap',
            'pdf' => 'application/pdf',
            'pfx' => 'application/x-pkcs12',
            'pgm' => 'image/x-portable-graymap',
            'pko' => 'application/ynd.ms-pkipko',
            'pma' => 'application/x-perfmon',
            'pmc' => 'application/x-perfmon',
            'pml' => 'application/x-perfmon',
            'pmr' => 'application/x-perfmon',
            'pmw' => 'application/x-perfmon',
            'pnm' => 'image/x-portable-anymap',
            'pot' => 'application/vnd.ms-powerpoint',
            'ppm' => 'image/x-portable-pixmap',
            'pps' => 'application/vnd.ms-powerpoint',
            'ppt' => 'application/vnd.ms-powerpoint',
            'prf' => 'application/pics-rules',
            'ps' => 'application/postscript',
            'pub' => 'application/x-mspublisher',
            'qt' => 'video/quicktime',
            'ra' => 'audio/x-pn-realaudio',
            'ram' => 'audio/x-pn-realaudio',
            'ras' => 'image/x-cmu-raster',
            'rgb' => 'image/x-rgb',
            'rmi' => 'audio/mid',
            'roff' => 'application/x-troff',
            'rtf' => 'application/rtf',
            'rtx' => 'text/richtext',
            'scd' => 'application/x-msschedule',
            'sct' => 'text/scriptlet',
            'setpay' => 'application/set-payment-initiation',
            'setreg' => 'application/set-registration-initiation',
            'sh' => 'application/x-sh',
            'shar' => 'application/x-shar',
            'sit' => 'application/x-stuffit',
            'snd' => 'audio/basic',
            'spc' => 'application/x-pkcs7-certificates',
            'spl' => 'application/futuresplash',
            'src' => 'application/x-wais-source',
            'sst' => 'application/vnd.ms-pkicertstore',
            'stl' => 'application/vnd.ms-pkistl',
            'stm' => 'text/html',
            'svg' => 'image/svg+xml',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc' => 'application/x-sv4crc',
            't' => 'application/x-troff',
            'tar' => 'application/x-tar',
            'tcl' => 'application/x-tcl',
            'tex' => 'application/x-tex',
            'texi' => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz' => 'application/x-compressed',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'tr' => 'application/x-troff',
            'trm' => 'application/x-msterminal',
            'tsv' => 'text/tab-separated-values',
            'txt' => 'text/plain',
            'uls' => 'text/iuls',
            'ustar' => 'application/x-ustar',
            'vcf' => 'text/x-vcard',
            'vrml' => 'x-world/x-vrml',
            'wav' => 'audio/x-wav',
            'wcm' => 'application/vnd.ms-works',
            'wdb' => 'application/vnd.ms-works',
            'wks' => 'application/vnd.ms-works',
            'wmf' => 'application/x-msmetafile',
            'wps' => 'application/vnd.ms-works',
            'wri' => 'application/x-mswrite',
            'wrl' => 'x-world/x-vrml',
            'wrz' => 'x-world/x-vrml',
            'xaf' => 'x-world/x-vrml',
            'xbm' => 'image/x-xbitmap',
            'xla' => 'application/vnd.ms-excel',
            'xlc' => 'application/vnd.ms-excel',
            'xlm' => 'application/vnd.ms-excel',
            'xls' => 'application/vnd.ms-excel',
            'xlt' => 'application/vnd.ms-excel',
            'xlw' => 'application/vnd.ms-excel',
            'xof' => 'x-world/x-vrml',
            'xpm' => 'image/x-xpixmap',
            'xwd' => 'image/x-xwindowdump',
            'z' => 'application/x-compress',
            'zip' => 'application/zip'
        );
        if (isset($mimeTypes[$ext])) {
            $contentType = $mimeTypes[$ext];
        } else {
            $contentType = 'octet/application-stream';
        }
        return $contentType;
    }

    /**
     * Move a file or folder to a specific location
     *
     * @param string $from The location to move from
     * @param string $to The location to move to
     * @param string $point
     * @return boolean
     */
    public function moveObject($from,$to,$point = 'append') {
        $this->xpdo->lexicon->load('source');
        $success = false;

        try {
            $obj_from = new CF_Object($this->container,$from,true);
        }
        catch (NoSuchObjectException $e) {
            $this->addError('file',$this->xpdo->lexicon('file_err_ns').': '.$from);
            return false;
        }
         
        if ($to != '/') {
            try {
                $obj_to = new CF_Object($this->container,trim($to,'/'),true);
            }    
            catch (NoSuchObjectException $e) {
                $this->addError('file',$this->xpdo->lexicon('file_err_ns').': '.$to);
                return false;
            }
            $toPath = $obj_to->name.'/'.basename($from);
        } else {
            $toPath = basename($from);
        }
        
        $moved = $this->container->move_object_to($obj_from, $this->container, $toPath);
        if (!$moved) {
            $this->xpdo->error->message = $this->xpdo->lexicon('file_folder_err_rename').': '.$to.' -> '.$from;
        }

        return $moved;
    }

    /**
     * @return array
     */
    public function getDefaultProperties() {
        return array(
            'url' => array(
                'name' => 'infodbfiles.url',
                'desc' => 'infodbfiles.url_desc', 
                'type' => 'textfield',
                'options' => '',
                'value' => '',
                'lexicon' => 'infodbfiles:settings',
            ),
            'api_key' => array(
                'name' => 'infodbfiles.api_key',
                'desc' => 'infodbfiles.api_key_desc',
                'type' => 'password',
                'options' => '',
                'value' => '',
                'lexicon' => 'infodbfiles:settings',
            ),
            'root_id' => array(
                'name' => 'infodbfiles.root_id',
                'desc' => 'infodbfiles.root_id_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '',
                'lexicon' => 'infodbfiles:settings',
            ),
            'imageExtensions' => array(
                'name' => 'infodbfiles.imageExtensions',
                'desc' => 'infodbfiles.imageExtensions_desc',
                'type' => 'textfield',
                'value' => 'jpg,jpeg,png,gif',
                'lexicon' => 'infodbfiles:settings',
            ),
            'thumbnailType' => array(
                'name' => 'infodbfiles.thumbnailType',
                'desc' => 'infodbfiles.thumbnailType_desc',
                'type' => 'list',
                'options' => array(
                    array('name' => 'PNG','value' => 'png'),
                    array('name' => 'JPG','value' => 'jpg'),
                    array('name' => 'GIF','value' => 'gif'),
                ),
                'value' => 'png',
                'lexicon' => 'infodbfiles:settings',
            ),
            'thumbnailQuality' => array(
                'name' => 'infodbfiles.thumbnailQuality',
                'desc' => 'infodbfiles.thumbnailQuality_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => 90,
                'lexicon' => 'infodbfiles:settings',
            ),
            'skipFiles' => array(
                'name' => 'infodbfiles.skipFiles',
                'desc' => 'infodbfiles.skipFiles_desc',
                'type' => 'textfield',
                'options' => '',
                'value' => '.svn,.git,_notes,nbproject,.idea,.DS_Store',
                'lexicon' => 'infodbfiles:settings',
            ),
        );
    }

    /**
     * Prepare a src parameter to be rendered with phpThumb
     * 
     * @param string $src
     * @return string
     */
    public function prepareSrcForThumb($src) {
        $properties = $this->getPropertyList();
        if (strpos($src,$properties['url']) === false) {
            $src = $properties['url'].ltrim($src,'/');
        }
        return $src;
    }

    /**
     * Get the base URL for this source. Only applicable to sources that are streams.
     *
     * @param string $object An optional object to find the base url of
     * @return string
     */
    public function getBaseUrl($object = '') {
        $properties = $this->getPropertyList();
        return $properties['url'];
    }

    /**
     * Get the absolute URL for a specified object. Only applicable to sources that are streams.
     *
     * @param string $object
     * @return string
     */
    public function getObjectUrl($object = '') {
        $properties = $this->getPropertyList();
        return $properties['url'].$object;
    }


    /**
     * Get the contents of a specified file
     *
     * @param string $objectPath
     * @return array
     */
    public function getObjectContents($objectPath) {
        $properties = $this->getPropertyList();
        try {
            $obj = new CF_Object($this->container,$objectPath, true);
            $contents = $obj->read();
            $last_modified = $obj->last_modified;
            $size = $obj->content_length;
        }
        catch (Exception $e) {
            $contents = '';
            $last_modified = '';
            $size = '';
        }
        
        $imageExtensions = $this->getOption('imageExtensions',$this->properties,'jpg,jpeg,png,gif');
        $imageExtensions = explode(',',$imageExtensions);
        $fileExtension = pathinfo($objectPath,PATHINFO_EXTENSION);
        
        return array(
            'name' => $objectPath,
            'basename' => basename($objectPath),
            'path' => $objectPath,
            'size' => $size,
            'last_accessed' => '',
            'last_modified' => $last_modified,
            'content' => $contents,
            'image' => in_array($fileExtension,$imageExtensions) ? true : false,
            'is_writable' => true,
            'is_readable' => true,
        );
    }
}