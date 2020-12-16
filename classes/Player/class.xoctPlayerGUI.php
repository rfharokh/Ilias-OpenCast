<?php

use srag\DIC\OpenCast\Exception\DICException;
use srag\Plugins\Opencast\Chat\GUI\ChatGUI;
use srag\Plugins\Opencast\Chat\GUI\ChatHistoryGUI;
use srag\Plugins\Opencast\Chat\Model\ChatroomAR;
use srag\Plugins\Opencast\Chat\Model\MessageAR;
use srag\Plugins\Opencast\Chat\Model\TokenAR;
use srag\Plugins\Opencast\Model\Config\PublicationUsage\PublicationUsage;
use srag\Plugins\Opencast\Model\Config\PublicationUsage\PublicationUsageRepository;

/**
 * Class xoctPlayerGUI
 *
 * @author Theodor Truffer <tt@studer-raimann.ch>
 */
class xoctPlayerGUI extends xoctGUI
{
    const CMD_STREAM_VIDEO = 'streamVideo';
    const CMD_DELIVER_VIDEO = 'deliverVideo';

    const IDENTIFIER = 'eid';

    const ROLE_MASTER = "presenter";
    const ROLE_SLAVE = "presentation";
    /**
     * @var xoctOpenCast
     */
    protected $xoctOpenCast;
    /**
     * @var PublicationUsageRepository
     */
    protected $publication_usage_repository;


    /**
     * @param xoctOpenCast $xoctOpenCast
     */
    public function __construct(xoctOpenCast $xoctOpenCast = NULL) {
        $this->publication_usage_repository = new PublicationUsageRepository();
        $this->xoctOpenCast = $xoctOpenCast instanceof xoctOpenCast ? $xoctOpenCast : new xoctOpenCast();
    }


    /**
     * @throws DICException
     * @throws arException
     * @throws ilTemplateException
     */
    public function streamVideo() {
        $xoctEvent = xoctEvent::find(filter_input(INPUT_GET, self::IDENTIFIER));

        $tpl = self::plugin()->getPluginObject()->getTemplate("paella_player.html", true, true);

        $tpl->setVariable("TITLE", $xoctEvent->getTitle());
        $tpl->setVariable("PAELLA_PLAYER_FOLDER", self::plugin()->getPluginObject()->getDirectory() . "/node_modules/paellaplayer/build/player");

        $js_config = new stdClass();
        $js_config->paella_config_file = self::plugin()->getPluginObject()->getDirectory() . "/js/paella_player/config"
            . ($xoctEvent->isLiveEvent() ? "_live" : "") . ".json";
        $js_config->paella_player_folder = self::plugin()->getPluginObject()->getDirectory() . "/node_modules/paellaplayer/build/player";

        try {
            $data = $xoctEvent->isLiveEvent() ? $this->getLiveStreamingData($xoctEvent) : $this->getStreamingData($xoctEvent);
            // dev: local streaming server
//            $data = [
//              'streams' => [
//                  [
//                      'content' => self::ROLE_MASTER,
//                      'sources' => [
//                          "hls" => [
//                              [
//                                  "src" => 'http://localhost:8080/hls/hello.m3u8',
//                                  "mimetype" => 'video/mp4',
//                                  "isLiveStream" => true
//                              ]
//                          ]
//                      ]
//                  ]
//              ]
//            ];
        } catch (xoctException $e) {
            echo $e->getMessage();
            exit;
        }

        $tpl->setVariable("DATA", json_encode($data));

        $js_config->is_live_stream = $xoctEvent->isLiveEvent();

        $start = 0;
        $end = 0;
        if ($xoctEvent->isLiveEvent()) {
            $start = $xoctEvent->getScheduling()->getStart()->getTimestamp();
            $end = $xoctEvent->getScheduling()->getEnd()->getTimestamp();
            $tpl->setVariable('LIVE_WAITING_TEXT', self::plugin()->translate('live_waiting_text', 'event', [date('H:i', $start)]));
            $tpl->setVariable('LIVE_INTERRUPTED_TEXT', self::plugin()->translate('live_interrupted_text', 'event'));
            $tpl->setVariable('LIVE_OVER_TEXT', self::plugin()->translate('live_over_text', 'event'));

            $js_config->check_script_hls = self::plugin()->directory() . '/src/Util/check_hls_status.php'; // used for live stream
        }

        $js_config->event_start = $start;
        $js_config->event_end = $end;
        $tpl->setVariable("JS_CONFIG", json_encode($js_config));

        if (!filter_input(INPUT_GET, 'force_no_chat') && xoctConf::getConfig(xoctConf::F_ENABLE_CHAT) && $this->xoctOpenCast->isChatActive()) {
            $ChatroomAR = ChatroomAR::findBy($xoctEvent->getIdentifier(), $this->xoctOpenCast->getObjId());
            if ($xoctEvent->isLiveEvent()) {
                $tpl->setVariable("STYLE_SHEET_LOCATION", ILIAS_HTTP_PATH . '/' . self::plugin()->getPluginObject()->getDirectory() . "/templates/default/player_w_chat.css");
                $ChatroomAR = ChatroomAR::findOrCreate($xoctEvent->getIdentifier(), $this->xoctOpenCast->getObjId());
                $public_name = self::dic()->user()->hasPublicProfile() ?
                    self::dic()->user()->getFirstname() . " " . self::dic()->user()->getLastname()
                    : self::dic()->user()->getLogin();
                $TokenAR = TokenAR::getNewFrom($ChatroomAR->getId(), self::dic()->user()->getId(), $public_name);
                $ChatGUI = new ChatGUI($TokenAR);
                $tpl->setVariable('CHAT', $ChatGUI->render(true));
            } elseif ($ChatroomAR && MessageAR::where(["chat_room_id" => $ChatroomAR->getId()])->hasSets()) {
                $tpl->setVariable("STYLE_SHEET_LOCATION", ILIAS_HTTP_PATH . '/' . self::plugin()->getPluginObject()->getDirectory() . "/templates/default/player_w_chat.css");
                $ChatHistoryGUI = new ChatHistoryGUI($ChatroomAR->getId());
                $tpl->setVariable('CHAT', $ChatHistoryGUI->render(true));
            }
        } else {
            $tpl->setVariable("STYLE_SHEET_LOCATION", ILIAS_HTTP_PATH . '/' . self::plugin()->getPluginObject()->getDirectory() . "/templates/default/player.css");
        }

        echo $tpl->get();

        exit();
    }


    /**
     * @param xoctEvent $xoctEvent
     *
     * @return array
     * @throws xoctException
     */
    protected function getStreamingData(xoctEvent $xoctEvent) {
        $medias = $xoctEvent->publications()->getPlayerPublications();
        $medias = array_values(array_filter($medias, function (xoctMedia $media) {
            return strpos($media->getMediatype(), xoctMedia::MEDIA_TYPE_VIDEO) !== false;
        }));

        if (empty($medias)) {
            throw new xoctException(xoctException::NO_STREAMING_DATA);
        }

        $previews = $xoctEvent->publications()->getPreviewPublications();

        $previews = array_reduce($previews, function (array &$previews, $preview) {
            /** @var $preview xoctPublication|xoctMedia|xoctAttachment */
            $previews[explode("/", $preview->getFlavor())[0]] = $preview;
            return $previews;
        }, []);

        $id = $xoctEvent->getIdentifier();
        $duration = 0;

        $streams = $this->buildStreams($medias, $duration, $previews, $id);

        if (xoctConf::getConfig(xoctConf::F_USE_STREAMING)) {
            $filteredStreams = [];
            foreach ($streams as $stream) {
                $filteredStreams[$stream['content']] = $stream;
            }

            $streams = [];
            foreach ($filteredStreams as $stream) {
                $streams[] = $stream;
            }
        }

        $data = [
            "streams" => $streams,
            "metadata" => [
                "title" => $xoctEvent->getTitle(),
                "duration" => $duration
            ]
        ];

        $segments = $this->buildSegments($xoctEvent);
        $data['frameList'] = $segments;

        return $data;
    }

    /**
     * @param xoctEvent $xoctEvent
     * @return array
     * @throws xoctException
     */
    protected function getLiveStreamingData(xoctEvent $xoctEvent) {
        $episode_json = xoctRequest::root()->episodeJson($xoctEvent->getIdentifier())->get([], '', xoctConf::getConfig(xoctConf::F_PRESENTATION_NODE));
        $episode_data = json_decode($episode_json, true);
        $media_package = $episode_data['search-results']['result']['mediapackage'];

        $streams = [];
        if( isset($media_package['media']['track'][0]))
        {
            foreach ($media_package['media']['track'] as $track) {
                $streams[] = [
                    "content" => (strpos($track['type'], self::ROLE_MASTER) !== false ? self::ROLE_MASTER : self::ROLE_SLAVE),
                    "sources" => [
                        "hls" => [
                            [
                                "src" => $track['url'],
                                "mimetype" => $track['mimetype'],
                                "isLiveStream" => true
                            ]
                        ]
                    ]
                ];
            }
        } else {
            $track = $media_package['media']['track'];
            $streams[] = [
                "content" => self::ROLE_MASTER,
                "sources" => [
                    "hls" => [
                        [
                            "src" => $track['url'],
                            "mimetype" => $track['mimetype'],
                            "isLiveStream" => true
                        ]
                    ]
                ]
            ];
        }

        return [
            "streams" => $streams,
            "metadata" => [
                "title" => $xoctEvent->getTitle(),
            ],
        ];
    }



    /**
     *
     */
    protected function deliverVideo() {
        $event_id = $_GET['event_id'];
        $mid = $_GET['mid'];
        $duration = 0;
        $xoctEvent = xoctEvent::find($event_id);
        $media = $xoctEvent->publications()->getFirstPublicationMetadataForUsage($this->publication_usage_repository->getUsage(PublicationUsage::USAGE_PLAYER))->getMedia();
        foreach ($media as $medium) {
            if ($medium->getId() == $mid) {
                $url = $medium->getUrl();
                $duration = $duration ?: $medium->getDuration();
                break;
            }
        }
        if (xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS)) {
            $url = xoctSecureLink::signPlayer($url, $duration);
        }
        //		$ctype= 'video/mp4';
        //		header('Content-Type: ' . $ctype);
        //		$handle = fopen($url, "rb");
        //		fpassthru($handle);
        //		$contents = fread($handle, filesize(()));
        //		fclose($handle);
        //		echo $contents;
        header("Location: " . $url);
        exit;

        // this request fetches the filesize. Better cache filesize to reduce loading time
        ini_set('max_execution_time', 0);
        $useragent = "Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.96 Safari/537.36";
        $v = $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 222222);
        curl_setopt($ch, CURLOPT_URL, $v);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $info = curl_exec($ch);
        $size2 = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
        header("Content-Type: video/mp4");


        $filesize = $size2;
        $offset = 0;
        $length = $filesize;
        if (isset($_SERVER['HTTP_RANGE'])) {
            $partialContent = "true";
            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
            $offset = intval($matches[1]);
            $length = $size2 - $offset - 1;
        } else {
            $partialContent = "false";
        }
        if ($partialContent == "true") {
            header('HTTP/1.1 206 Partial Content');
            header('Accept-Ranges: bytes');
            header('Content-Range: bytes '.$offset.
                '-'.($offset + $length).
                '/'.$filesize);
        } else {
            header('Accept-Ranges: bytes');
        }
        header("Content-length: ".$size2);


        $ch = curl_init();
        if (isset($_SERVER['HTTP_RANGE'])) {
            // if the HTTP_RANGE header is set we're dealing with partial content
            $partialContent = true;
            // find the requested range
            // this might be too simplistic, apparently the client can request
            // multiple ranges, which can become pretty complex, so ignore it for now
            preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
            $offset = intval($matches[1]);
            $length = $filesize - $offset - 1;
            $headers = array(
                'Range: bytes='.$offset.
                '-'.($offset + $length).
                ''
            );
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 222222);
        curl_setopt($ch, CURLOPT_URL, $v);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_exec($ch);
        exit;
        //		echo $out;
    }

    /**
     * @param $key
     *
     * @return string
     * @throws DICException
     */
    public function txt($key) {
        return self::plugin()->translate('event_' . $key);
    }


    protected function index()
    {
    }


    protected function add()
    {
    }


    protected function create()
    {
    }


    protected function edit()
    {
    }


    protected function update()
    {
    }


    protected function confirmDelete()
    {
    }


    protected function delete()
    {
    }


    /**
     * @param xoctMedia[]               $media
     * @param int       &$duration
     * @param xoctPublicationMetadata[] $previews
     * @param string                    $id
     * @return array|int
     * @throws xoctException
     */
    function buildStreams(array $media, int &$duration, array $previews, string $id)
    {
        $streams = [];
        foreach ($media as $medium) {
            $duration = $duration ?: $medium->getDuration();
            $url = xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS) ? xoctSecureLink::signPlayer($medium->getUrl(), $duration) : $medium->getUrl();
            $preview_url = $previews[$medium->getRole()];
            if ($preview_url !== null) {
                $preview_url = xoctConf::getConfig(xoctConf::F_SIGN_THUMBNAIL_LINKS) ? xoctSecureLink::signThumbnail($preview_url->getUrl()) : $preview_url->getUrl();
            } else {
                $preview_url = "";
            }

            if (xoctConf::getConfig(xoctConf::F_USE_STREAMING)) {

                $smil_url_identifier = ($medium->getRole() !== xoctMedia::ROLE_PRESENTATION ? "_presenter" : "_presentation");
                $streaming_server_url = xoctConf::getConfig(xoctConf::F_STREAMING_URL);
                $hls_url = $streaming_server_url . "/smil:engage-player_" . $id . $smil_url_identifier . ".smil/playlist.m3u8";
                $dash_url = $streaming_server_url . "/smil:engage-player_" . $id . $smil_url_identifier . ".smil/manifest_mpm4sav_mvlist.mpd";

                if (xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS)) {
                    $hls_url = xoctSecureLink::signPlayer($hls_url, $duration);
                    $dash_url = xoctSecureLink::signPlayer($dash_url, $duration);
                }

                $streams[] = [
                    "type" => xoctMedia::MEDIA_TYPE_VIDEO,
                    "content" => ($medium->getRole() !== xoctMedia::ROLE_PRESENTATION ? self::ROLE_MASTER : self::ROLE_SLAVE),
                    "sources" => [
                        "hls" => [
                            [
                                "src" => $hls_url,
                                "mimetype" => "application/x-mpegURL"
                            ],
                        ],
                        "dash" => [
                            [
                                "src" => $dash_url,
                                "mimetype" => "application/dash+xml"
                            ]
                        ]
                    ],
                    "preview" => $preview_url
                ];
            } else {
                $streams[] = [
                    "type" => xoctMedia::MEDIA_TYPE_VIDEO,
                    "content" => ($medium->getRole() !== xoctMedia::ROLE_PRESENTATION ? self::ROLE_MASTER : self::ROLE_SLAVE),
                    "sources" => [
                        "mp4" => [
                            [
                                "src" => $url,
                                "mimetype" => $medium->getMediatype(),
                                "res" => [
                                    "w" => $medium->getWidth(),
                                    "h" => $medium->getHeight()
                                ]
                            ]
                        ]

                    ],
                    "preview" => $preview_url
                ];
            }
        }
        return $streams;
    }

    /**
     * @param xoctEvent $xoctEvent
     *
     * @return array
     * @throws xoctException
     */
    protected function buildSegments(xoctEvent $xoctEvent) : array
    {
        $frameList = [];
        $segments = $xoctEvent->publications()->getSegmentPublications();
        if (count($segments) > 0) {
            $segments = array_reduce($segments, function (array &$segments, xoctAttachment $segment) {
                if (!isset($segments[$segment->getRef()])) {
                    $segments[$segment->getRef()] = [];
                }
                $segments[$segment->getRef()][$segment->getFlavor()] = $segment;

                return $segments;
            }, []);

            ksort($segments);
            $frameList = array_values(array_map(function (array $segment) {

                if (xoctConf::getConfig(xoctConf::F_USE_HIGH_LOW_RES_SEGMENT_PREVIEWS)) {
                    /**
                     * @var xoctAttachment[] $segment
                     */
                    $high = $segment[Metadata::FLAVOR_PRESENTATION_SEGMENT_PREVIEW_HIGHRES];
                    $low = $segment[Metadata::FLAVOR_PRESENTATION_SEGMENT_PREVIEW_LOWRES];
                    if ($high === null || $low === null) {
                        $high = $segment[Metadata::FLAVOR_PRESENTER_SEGMENT_PREVIEW_HIGHRES];
                        $low = $segment[Metadata::FLAVOR_PRESENTER_SEGMENT_PREVIEW_LOWRES];
                    }

                    $time = substr($high->getRef(), strpos($high->getRef(), ";time=") + 7, 8);
                    $time = new DateTime("1970-01-01 $time", new DateTimeZone("UTC"));
                    $time = $time->getTimestamp();

                    $high_url = $high->getUrl();
                    $low_url = $low->getUrl();
                    if (xoctConf::getConfig(xoctConf::F_SIGN_THUMBNAIL_LINKS)) {
                        $high_url = xoctSecureLink::signThumbnail($high_url);
                        $low_url = xoctSecureLink::signThumbnail($low_url);
                    }

                    return [
                        "id"       => "frame_" . $time,
                        "mimetype" => $high->getMediatype(),
                        "time"     => $time,
                        "url"      => $high_url,
                        "thumb"    => $low_url
                    ];
                } else {
                    $preview = $segment[Metadata::FLAVOR_PRESENTATION_SEGMENT_PREVIEW];

                    if ($preview === null) {
                        $preview = $segment[Metadata::FLAVOR_PRESENTER_SEGMENT_PREVIEW];
                    }

                    $time = substr($preview->getRef(), strpos($preview->getRef(), ";time=") + 7, 8);
                    $time = new DateTime("1970-01-01 $time", new DateTimeZone("UTC"));
                    $time = $time->getTimestamp();

                    $url = $preview->getUrl();
                    if (xoctConf::getConfig(xoctConf::F_SIGN_THUMBNAIL_LINKS)) {
                        $url = xoctSecureLink::signThumbnail($url);
                    }

                    return [
                        "id"       => "frame_" . $time,
                        "mimetype" => $preview->getMediatype(),
                        "time"     => $time,
                        "url"      => $url,
                        "thumb"    => $url
                    ];
                }
            }, $segments));
        }

        return $frameList;
    }
}
