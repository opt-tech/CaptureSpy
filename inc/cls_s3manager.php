<?php
    //ini_set("display_errors", 1);
    use Aws\S3\S3Client;
    use Aws\Common\Enum\Region;
    use Aws\S3\Exception\S3Exception;
    use Guzzle\Http\EntityBody;


    /**
     * Created by PhpStorm.
     * User: ka.kubota
     * Date: 2016/09/16
     * Time: 10:26
     */
    class S3manager {
        var     $config;
        private $remoteDir;
        private $fileName;
        private $localFilePath;
        private $remoteFilePath;
        private $client;


        public function __construct($AWS_CONFIG, $remoteDir = NULL, $fileName = NULL) {
            $this->config = $AWS_CONFIG;
            $this->setFileName($fileName);
            $this->setRemoteDir($remoteDir);

            $this->client = S3Client::factory($AWS_CONFIG);

        }


        /**
         * @return mixed
         */
        public function getFileName() {
            return $this->fileName;
        }


        /**
         * @param mixed $fileName
         */
        public function setFileName($fileName) {

            $this->localFilePath = TEMP_DIR . $fileName;
            $this->fileName = $fileName;

            $this->setRemoteDir($this->remoteDir);
        }


        /**
         * @return mixed
         */
        public function getRemoteDir() {
            return $this->remoteDir;
        }


        /**
         * @param mixed $remoteDir
         */
        public function setRemoteDir($remoteDir) {
            $this->remoteDir = $remoteDir;
            $this->remoteFilePath = $remoteDir . $this->fileName;
        }


        public function upload() {
            try {
                (new Logger('crawler', 'info'))->Out("upload excel({$this->localFilePath}) to " . $this->remoteFilePath);
                $result = $this->client->putObject([
                                                       'Bucket' => $this->config['bucket'],
                                                       'Key'    => $this->remoteFilePath,
                                                       'Body'   => EntityBody::factory(fopen($this->localFilePath, 'r')),
                                                   ]);
            } catch (S3Exception $exc) {
                return $exc->getMessage();
            }

            return "";
        }


        public function download() {
            try {
                $result = $this->client->getObject([
                                                       'Bucket' => $this->config['bucket'],
                                                       'Key'    => $this->remoteFilePath,
                                                       'SaveAs' => $this->localFilePath,
                                                   ]);
            } catch (S3Exception $exc) {
                return $exc->getMessage();
            }

            return "";
        }


        public function downloadDirect($remote, $local) {
            try {
                $result = $this->client->getObject([
                                                       'Bucket' => $this->config['bucket'],
                                                       'Key'    => $remote,
                                                       'SaveAs' => $local,
                                                   ]);
            } catch (S3Exception $exc) {
                return $exc->getMessage();
            }

            return "";
        }


        public function DirectDownload($remoteFile) {
            try {
                $result = $this->client->getObject([
                                                       'Bucket' => $this->config['bucket'],
                                                       'Key'    => $remoteFile,
                                                   ]);

                header("Content-Type: {$result['ContentType']}");
                header('Content-Disposition: attachment; filename="' . basename($remoteFile) . '"');
                echo $result['Body'];
            } catch (S3Exception $exc) {
                return $exc->getMessage();
            }

            return "";
        }


        public function exits($fileName = NULL) {

            $fileName = Util::nz($fileName, $this->fileName);

            $response = $this->client->listObjects([
                                                       'Bucket' => $this->config['bucket'],
                                                       'Prefix' => $this->remoteDir . $fileName,
                                                   ]);

            return (count($response["Contents"]) > 0);

        }

        public function exitsFullpath($path) {
            $response = $this->client->listObjects([
                                                       'Bucket' => $this->config['bucket'],
                                                       'Prefix' => $path,
                                                   ]);

            return (count($response["Contents"]) > 0);

        }


        public function read($remoteFile) {
            if($remoteFile == "") return "対象ファイルが存在しませんでした";
            try {

                if($this->exitsFullpath($remoteFile)) {
                    $result = $this->client->getObject([
                                                           'Bucket' => $this->config['bucket'],
                                                           'Key'    => $remoteFile,
                                                       ]);
                    return $result['Body'];
                } else {
                    return "";
                }
            } catch (S3Exception $exc) {
                print "error:$exc";

                return "";
            }

            return "";

        }

    }