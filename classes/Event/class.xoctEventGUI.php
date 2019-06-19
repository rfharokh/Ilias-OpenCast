<?php

/**
 * Class xoctEventGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy xoctEventGUI: ilObjOpenCastGUI
 */
class xoctEventGUI extends xoctGUI {

	const IDENTIFIER = 'eid';
	const CMD_SHOW_CONTENT = 'showContent';
	const CMD_CLEAR_CACHE = 'clearCache';
	const CMD_EDIT_OWNER = 'editOwner';
	const CMD_UPDATE_OWNER = 'updateOwner';
	const CMD_UPLOAD_CHUNKS = 'uploadChunks';
	const CMD_SET_ONLINE = 'setOnline';
	const CMD_SET_OFFLINE = 'setOffline';
	const CMD_CUT = 'cut';
	const CMD_ANNOTATE = 'annotate';
	const CMD_REPORT_DATE = 'reportDate';
	const CMD_REPORT_QUALITY = 'reportQuality';
	const CMD_SCHEDULE = 'schedule';
	const CMD_CREATE_SCHEDULED = 'createScheduled';
    	const CMD_DELIVER_VIDEO = 'deliverVideo';
	const CMD_STREAM_VIDEO = 'streamVideo';
	const ROLE_MASTER = "presenter";
	const ROLE_SLAVE = "presentation";

	/**
	 * @var xoctOpenCast
	 */
	protected $xoctOpenCast;


	/**
	 * @param xoctOpenCast $xoctOpenCast
	 */
	public function __construct(xoctOpenCast $xoctOpenCast = NULL) {
		if ($xoctOpenCast instanceof xoctOpenCast) {
			$this->xoctOpenCast = $xoctOpenCast;
		} else {
			$this->xoctOpenCast = new xoctOpenCast();
		}
		self::dic()->tabs()->setTabActive(ilObjOpenCastGUI::TAB_EVENTS);
		self::dic()->mainTemplate()->addCss('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/templates/default/events.css');
		self::dic()->mainTemplate()->addJavaScript('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/templates/default/events.js');
	}


	/**
	 * @param $cmd
	 */
	protected function performCommand($cmd) {
		switch ($cmd) {
			case self::CMD_STANDARD:
			case self::CMD_SHOW_CONTENT:
				$this->prepareContent();
				break;
			default:
		}
		parent::performCommand($cmd);
	}


	/**
	 *
	 */
	protected function prepareContent() {
		// init waiter
		xoctWaiterGUI::initJS();
		xoctWaiterGUI::addLinkOverlay('#rep_robj_xoct_event_clear_cache');

		// add "add" button
		if (ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_ADD_EVENT)) {
			$b = ilLinkButton::getInstance();
			$b->setCaption('rep_robj_xoct_event_add_new');
			$b->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_ADD));
			$b->setPrimary(true);
			self::dic()->toolbar()->addButtonInstance($b);
		}

		// add "schedule" button
		if (ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_ADD_EVENT) && xoctConf::getConfig(xoctConf::F_CREATE_SCHEDULED_ALLOWED)) {
			$b = ilLinkButton::getInstance();
			$b->setCaption('rep_robj_xoct_event_schedule_new');
			$b->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_SCHEDULE));
			$b->setPrimary(true);
			self::dic()->toolbar()->addButtonInstance($b);
		}

		// add "clear cache" button
		if (xoctConf::getConfig(xoctConf::F_ACTIVATE_CACHE)) {
			$b = ilLinkButton::getInstance();
			$b->setId('rep_robj_xoct_event_clear_cache');
			$b->setCaption('rep_robj_xoct_event_clear_cache');
			$b->setUrl(self::dic()->ctrl()->getLinkTarget($this, self::CMD_CLEAR_CACHE));
			self::dic()->toolbar()->addButtonInstance($b);
		}

		// add "clear clips" button (devmode)
		if (self::dic()->user()->getId() == 6 && ilObjOpenCast::DEV) {
			$b = ilLinkButton::getInstance();
			$b->setCaption('rep_robj_xoct_event_clear_clips_develop');
			$b->setUrl(self::dic()->ctrl()->getLinkTarget($this, 'clearAllClips'));
			self::dic()->toolbar()->addButtonInstance($b);
		}

		// add "report date change" button
		if (ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_REPORT_DATE_CHANGE)) {
			$b = ilButton::getInstance();
			$b->setId('xoct_report_date_button');
			$b->setCaption('rep_robj_xoct_event_report_date_modification');
			$b->setOnClick("$('#xoct_report_date_modal').modal('show');");
			$b->addCSSClass('hidden');

			self::dic()->toolbar()->addButtonInstance($b);
		}
	}


	/**
	 * same cmd as standard command (index()), except it's synchronous
	 */
	protected function showContent() {
		$intro_text = '';
		if ($this->xoctOpenCast->getIntroductionText()) {
			$intro = new ilTemplate('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/templates/default/tpl.intro.html', '', true, true);
			$intro->setVariable('INTRO', nl2br($this->xoctOpenCast->getIntroductionText()));
			$intro_text = $intro->get();
		}

		$xoctEventTableGUI = new xoctEventTableGUI($this, self::CMD_STANDARD, $this->xoctOpenCast, true);
        if ($xoctEventTableGUI->hasScheduledEvents()) {
            self::dic()->mainTemplate()->addOnLoadCode("$('#xoct_report_date_button').removeClass('hidden');");
        }
		self::dic()->mainTemplate()->setContent($intro_text . $xoctEventTableGUI->getHTML() . $this->getModalsHTML());
	}


	/**
	 * asynchronous loading of tableGUI
	 */
	protected function index() {
        ilChangeEvent::_recordReadEvent(
            $this->xoctOpenCast->getILIASObject()->getType(), $this->xoctOpenCast->getILIASObject()->getRefId(),
            $this->xoctOpenCast->getObjId(), self::dic()->user()->getId());

        $intro_text = '';
		if ($this->xoctOpenCast->getIntroductionText()) {
			$intro = new ilTemplate('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/templates/default/tpl.intro.html', '', true, true);
			$intro->setVariable('INTRO', nl2br($this->xoctOpenCast->getIntroductionText()));
			$intro_text = $intro->get();
		}

		if (isset($_GET[xoctEventTableGUI::getGeneratedPrefix($this->xoctOpenCast) . '_xpt']) || !empty($_POST)) {
			$xoctEventTableGUI = new xoctEventTableGUI($this, self::CMD_STANDARD, $this->xoctOpenCast);
            if ($xoctEventTableGUI->hasScheduledEvents()) {
                self::dic()->mainTemplate()->addOnLoadCode("$('#xoct_report_date_button').removeClass('hidden');");
            }
			self::dic()->mainTemplate()->setContent($intro_text . $xoctEventTableGUI->getHTML() . $this->getModalsHTML());
			return;
		}

		self::dic()->mainTemplate()->setContent($intro_text . '<div id="xoct_table_placeholder"></div>' . $this->getModalsHTML());
		self::dic()->mainTemplate()->addJavascript("./Services/Table/js/ServiceTable.js");
		$this->loadAjaxCode();
	}


	/**
	 *
	 */
	protected function loadAjaxCode() {
		foreach ($_GET as $para => $value) {
			self::dic()->ctrl()->setParameter($this, $para, $value);
		}

		$ajax_link = self::dic()->ctrl()->getLinkTarget($this, 'asyncGetTableGUI', "", true);

		// hacky stuff to allow asynchronous rendering of tableGUI
		$table_id = xoctEventTableGUI::getGeneratedPrefix($this->xoctOpenCast);
		$user_id = self::dic()->user()->getId();
		$tab_prop = new ilTablePropertiesStorage();
		if ($tab_prop->getProperty($table_id, $user_id, 'filter')) {
			$activate_filter_commmand = "ilShowTableFilter('tfil_$table_id', './ilias.php?baseClass=ilTablePropertiesStorage&table_id=$table_id&cmd=showFilter&user_id=$user_id');";
		}

		$ajax = "$.ajax({
				    url: '{$ajax_link}',
				    dataType: 'html',
				    success: function(data){
				        xoctWaiter.hide();
				        $('div#xoct_table_placeholder').replaceWith($(data));
				        $activate_filter_commmand
				    }
				});";
		self::dic()->mainTemplate()->addOnLoadCode('xoctWaiter.show();');
		self::dic()->mainTemplate()->addOnLoadCode($ajax);
	}


	/**
	 *
	 */
	public function asyncGetTableGUI() {
		$xoctEventTableGUI = new xoctEventTableGUI($this, self::CMD_STANDARD, $this->xoctOpenCast);
        $html = $xoctEventTableGUI->getHTML();
        if ($xoctEventTableGUI->hasScheduledEvents()) {
            $html .= "<script type='text/javascript'>$('#xoct_report_date_button').removeClass('hidden');</script>";
        }
        echo $html;
        exit();
	}


	/**
	 *
	 */
	protected function applyFilter() {
		$xoctEventTableGUI = new xoctEventTableGUI($this, self::CMD_STANDARD, $this->xoctOpenCast, false);
		$xoctEventTableGUI->resetOffset(true);
		$xoctEventTableGUI->writeFilterToSession();
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function resetFilter() {
		//		xoctEventTableGUI::setDefaultRowValue($this->xoctOpenCast);
		$xoctEventTableGUI = new xoctEventTableGUI($this, self::CMD_STANDARD, $this->xoctOpenCast, false);
		$xoctEventTableGUI->resetOffset();
		$xoctEventTableGUI->resetFilter();
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function add() {
		if ($this->xoctOpenCast->getDuplicatesOnSystem()) {
			ilUtil::sendInfo(self::plugin()->translate('series_has_duplicates_events'));
		}
		$xoctEventFormGUI = new xoctEventFormGUI($this, new xoctEvent(), $this->xoctOpenCast);
		$xoctEventFormGUI->fillForm();
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function create() {
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		$xoctEventFormGUI = new xoctEventFormGUI($this, new xoctEvent(), $this->xoctOpenCast);

		$xoctAclStandardSets = new xoctAclStandardSets($xoctUser->getOwnerRoleName() ? array($xoctUser->getOwnerRoleName(), $xoctUser->getUserRoleName()) : array());
		$xoctEventFormGUI->getObject()->setAcl($xoctAclStandardSets->getAcls());

		if ($xoctEventFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_created'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		$xoctEventFormGUI->setValuesByPost();
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function uploadChunks() {
		$xoctPlupload = new xoctPlupload();
		$xoctPlupload->handleUpload();
	}


	/**
	 *
	 */
	protected function schedule() {
		if ($this->xoctOpenCast->getDuplicatesOnSystem()) {
			ilUtil::sendInfo(self::plugin()->translate('series_has_duplicates_events'));
		}
		$xoctEventFormGUI = new xoctEventFormGUI($this, new xoctEvent(), $this->xoctOpenCast, true);
		$xoctEventFormGUI->fillForm();
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function createScheduled() {
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		$xoctEventFormGUI = new xoctEventFormGUI($this, new xoctEvent(), $this->xoctOpenCast, true);

		$xoctAclStandardSets = new xoctAclStandardSets($xoctUser->getOwnerRoleName() ? array($xoctUser->getOwnerRoleName(), $xoctUser->getUserRoleName()) : array());
		$xoctEventFormGUI->getObject()->setAcl($xoctAclStandardSets->getAcls());

		if ($xoctEventFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_scheduled'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		$xoctEventFormGUI->setValuesByPost();
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}

	/**
	 *
	 */
	protected function edit() {
		/**
		 * @var xoctEvent $xoctEvent
		 */
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		$xoctUser = xoctUser::getInstance(self::dic()->user());

		// check access
		if (!ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_EDIT_EVENT, $xoctEvent, $xoctUser)) {
			ilUtil::sendFailure($this->txt('msg_no_access'), true);
			$this->cancel();
		}

		$xoctEventFormGUI = new xoctEventFormGUI($this, $xoctEvent, $this->xoctOpenCast);
		$xoctEventFormGUI->fillForm();
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	public function cut() {
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);

		// check access
		if (!ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_CUT, $xoctEvent, $xoctUser)) {
			ilUtil::sendFailure($this->txt('msg_no_access'), true);
			$this->cancel();
		}

		// add user to ilias producers
		try {
			$ilias_producers = xoctGroup::find(xoctConf::getConfig(xoctConf::F_GROUP_PRODUCERS));
			$sleep = $ilias_producers->addMember($xoctUser);
		} catch (xoctException $e) {
			$sleep = false;
		}

		// add user to series producers
		/** @var xoctSeries $xoctSeries */
		$xoctSeries = xoctSeries::find($xoctEvent->getSeriesIdentifier());
		if ($xoctSeries->addProducer($xoctUser)) {
            $sleep = true;
        }

		if ($sleep) {
			sleep(3);
		}

		// redirect
		$cutting_link = $xoctEvent->getCuttingLink();
		header('Location: ' . $cutting_link);
	}

	/**
	 *
	 */
	public function annotate() {
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);

		// check access
		if (ilObjOpenCastAccess::hasPermission('edit_videos') || ilObjOpenCastAccess::hasWriteAccess()) {
            // add user to ilias producers
            try {
                $ilias_producers = xoctGroup::find(xoctConf::getConfig(xoctConf::F_GROUP_PRODUCERS));
                $sleep = $ilias_producers->addMember($xoctUser);
            } catch (xoctException $e) {
                $sleep = false;
            }

            // add user to series producers
            /** @var xoctSeries $xoctSeries */
            $xoctSeries = xoctSeries::find($xoctEvent->getSeriesIdentifier());
            if ($xoctSeries->addProducer($xoctUser)) {
                $sleep = true;
            }

            if ($sleep) {
                sleep(3);
            }
        }


		// redirect
		$cutting_link = $xoctEvent->getAnnotationLink();
		header('Location: ' . $cutting_link);
	}


	/**
	 *
	 */
	public function setOnline() {
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		$xoctEvent->getXoctEventAdditions()->setIsOnline(true);
		$xoctEvent->getXoctEventAdditions()->update();
		$this->cancel();
	}


	/**
	 *
	 */
	public function setOffline() {
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		$xoctEvent->getXoctEventAdditions()->setIsOnline(false);
		$xoctEvent->getXoctEventAdditions()->update();
		$this->cancel();
	}


	/**
	 *
	 */
	public function streamVideo() {
		$xoctEvent = xoctEvent::find(filter_input(INPUT_GET, self::IDENTIFIER));

		// check access
		if (!ilObjOpenCastAccess::hasReadAccessOnEvent($xoctEvent, xoctUser::getInstance(self::dic()->user()), $this->xoctOpenCast)) {
			ilUtil::sendFailure($this->txt("msg_no_access"), true);
			$this->cancel();
		}

		$publication_player = $xoctEvent->getFirstPublicationMetadataForUsage(xoctPublicationUsage::getUsage(xoctPublicationUsage::USAGE_PLAYER));

		// Multi stream
		$medias = array_values(array_filter($publication_player->getMedia(), function (xoctMedia $media) {
			return (strpos($media->getMediatype(), xoctMedia::MEDIA_TYPE_VIDEO) !== false
				&& in_array(xoctPublicationUsage::USAGE_ENGAGE_STREAMING, $media->getTags()));
		}));
		if (count($medias) === 0) {
			// Single stream
			$medias = array_values(array_filter($publication_player->getMedia(), function (xoctMedia $media) {
				return (strpos($media->getMediatype(), xoctMedia::MEDIA_TYPE_VIDEO) !== false
					&& in_array(xoctPublicationUsage::USAGE_ENGAGE_STREAMING, $media->getTags()));
			}));
		}

		/**
		 * @var xoctAttachment[] $previews
		 */
		$previews = array_filter($publication_player->getAttachments(), function (xoctAttachment $attachment) {
			return (strpos($attachment->getFlavor(), '/player+preview') !== false);
		});
		$previews = array_reduce($previews, function (array &$previews, xoctAttachment $preview) {
			$previews[explode("/", $preview->getFlavor())[0]] = $preview;

			return $previews;
		}, []);

		$duration = 0;

		$id = filter_input(INPUT_GET, self::IDENTIFIER);

		$streams = array_map(function (xoctMedia $media) use (&$duration, &$previews, &$id) {
			$url = $media->getUrl();
			if (xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS)) {
				$url = xoctSecureLink::sign($url);
			}

			$role = (strpos($media->getFlavor(), xoctMedia::ROLE_PRESENTATION) !== false ? xoctMedia::ROLE_PRESENTATION : xoctMedia::ROLE_PRESENTER);

			if ($duration == 0) {
				$duration = $media->getDuration();
			}

			$preview_url = $previews[$role];
			if ($preview_url !== NULL) {
				$preview_url = $preview_url->getUrl();
				if (xoctConf::getConfig(xoctConf::F_SIGN_THUMBNAIL_LINKS)) {
					$preview_url = xoctSecureLink::sign($preview_url);
				}
			} else {
				$preview_url = "";
			}



            if( xoctConf::getConfig(xoctConf::F_USE_STREAMING)) {

                $smil_url_identifier = ($role !== xoctMedia::ROLE_PRESENTATION ? "_presenter" : "_presentation");

                $streaming_server_url = xoctConf::getConfig(xoctConf::F_STREAMING_URL);

                $hls_url = $streaming_server_url . "/smil:engage-player_" . $id . $smil_url_identifier . ".smil/playlist.m3u8";

                $dash_url = $streaming_server_url . "/smil:engage-player_" . $id . $smil_url_identifier . ".smil/manifest_mpm4sav_mvlist.mpd";

	            if (xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS)) {

	            	$valid_until = null;

		            if (xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS_OVERWRITE_DEFAULT)) {
			            $duration_in_seconds = $duration / 1000;

			            $additional_time_percent = xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS_ADDITIONAL_TIME_PERCENT) / 100;

			            $valid_until = gmdate("Y-m-d\TH:i:s\Z", time() + $duration_in_seconds + $duration_in_seconds * $additional_time_percent);
		            }

		            $hls_url = xoctSecureLink::sign($hls_url, $valid_until);
		            $dash_url = xoctSecureLink::sign($dash_url, $valid_until);
	            }

                return [
                    "type" => xoctMedia::MEDIA_TYPE_VIDEO,
                    "content" => ($role !== xoctMedia::ROLE_PRESENTATION ? self::ROLE_MASTER : self::ROLE_SLAVE),
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
            }
            else{
                return [
                    "type" => xoctMedia::MEDIA_TYPE_VIDEO,
                    "content" => ($role !== xoctMedia::ROLE_PRESENTATION ? self::ROLE_MASTER : self::ROLE_SLAVE),
                    "sources" => [
                        "mp4" => [
                            [
                                "src" => $url,
                                "mimetype" => $media->getMediatype(),
                                "res" => [
                                    "w" => $media->getWidth(),
                                    "h" => $media->getHeight()
                                ]
                            ]
                        ]

                    ],
                    "preview" => $preview_url
                ];
            }
		}, $medias);

		if( xoctConf::getConfig(xoctConf::F_USE_STREAMING)) {

			$filteredStreams = array();
			foreach ( $streams as $stream)
			{
				$filteredStreams[$stream['content']] = $stream;
			}

			$streams = array();
			foreach ($filteredStreams as $stream)
			{
				$streams[] = $stream;
			}
		}

		$segmentFlavor = xoctPublicationUsage::find(xoctPublicationUsage::USAGE_SEGMENTS)->getFlavor();
		$publication_usage_segments = xoctPublicationUsage::getUsage(xoctPublicationUsage::USAGE_SEGMENTS);
		$attachments =
			$publication_usage_segments->getMdType() == xoctPublicationUsage::MD_TYPE_PUBLICATION_ITSELF ?
				$xoctEvent->getFirstPublicationMetadataForUsage($publication_usage_segments)->getAttachments() :
				$xoctEvent->getPublicationMetadataForUsage($publication_usage_segments);

		$segments = array_filter($attachments, function (xoctAttachment $attachment) use ( &$segmentFlavor)  {
			return strpos($attachment->getFlavor(), $segmentFlavor) !== FALSE;
		});

		$segments = array_reduce($segments, function (array &$segments, xoctAttachment $segment) {
			if (!isset($segments[$segment->getRef()])) {
				$segments[$segment->getRef()] = [];
			}
			$segments[$segment->getRef()][$segment->getFlavor()] = $segment;

			return $segments;
		}, []);

		ksort($segments);
		$frameList = array_values(array_map(function (array $segment) {

			if( xoctConf::getConfig(xoctConf::F_USE_HIGHLOWRESSEGMENTPREVIEWS)) {
				/**
				 * @var xoctAttachment[] $segment
				 */
				$high = $segment[xoctMetadata::FLAVOR_PRESENTATION_SEGMENT_PREVIEW_HIGHRES];
				$low = $segment[xoctMetadata::FLAVOR_PRESENTATION_SEGMENT_PREVIEW_LOWRES];
				if ($high === NULL || $low === NULL) {
					$high = $segment[xoctMetadata::FLAVOR_PRESENTER_SEGMENT_PREVIEW_HIGHRES];
					$low = $segment[xoctMetadata::FLAVOR_PRESENTER_SEGMENT_PREVIEW_LOWRES];
				}

				$time = substr($high->getRef(), strpos($high->getRef(), ";time=") + 7, 8);
				$time = new DateTime("1970-01-01 $time", new DateTimeZone("UTC"));
				$time = $time->getTimestamp();

				$high_url = $high->getUrl();
				$low_url = $low->getUrl();
				if (xoctConf::getConfig(xoctConf::F_SIGN_THUMBNAIL_LINKS)) {
					$high_url = xoctSecureLink::sign($high_url);
					$low_url = xoctSecureLink::sign($low_url);
				}

				return [
					"id" => "frame_" . $time,
					"mimetype" => $high->getMediatype(),
					"time" => $time,
					"url" => $high_url,
					"thumb" => $low_url
				];
			}
			else {
				$preview = $segment[xoctMetadata::FLAVOR_PRESENTATION_SEGMENT_PREVIEW];

				if ($preview === NULL) {
					$preview = $segment[xoctMetadata::FLAVOR_PRESENTER_SEGMENT_PREVIEW];
				}

				$time = substr($preview->getRef(), strpos($preview->getRef(), ";time=") + 7, 8);
				$time = new DateTime("1970-01-01 $time", new DateTimeZone("UTC"));
				$time = $time->getTimestamp();

				$url = $preview->getUrl();
				if (xoctConf::getConfig(xoctConf::F_SIGN_THUMBNAIL_LINKS)) {
					$url = xoctSecureLink::sign($url);

				}

				return [
					"id" => "frame_" . $time,
					"mimetype" => $preview->getMediatype(),
					"time" => $time,
					"url" => $url,
					"thumb" => $url
				];
			}
		}, $segments));

		$tpl = self::plugin()->getPluginObject()->getTemplate("paella_player.html", false, false);

		$tpl->setVariable("TITLE", $xoctEvent->getTitle());

		$tpl->setVariable("PAELLA_PLAYER_FOLDER", self::plugin()->getPluginObject()->getDirectory() . "/node_modules/paellaplayer/build/player");

		$tpl->setVariable("PAELLA_CONFIG_FILE", self::plugin()->getPluginObject()->getDirectory() . "/js/paella_player/config.json");

		$data = [
			"streams" => $streams,
			"frameList" => $frameList,
			"metadata" => [
				"title" => $xoctEvent->getTitle(),
				"duration" => $duration
			]
		];
		$tpl->setVariable("DATA", json_encode($data));

		echo $tpl->get();

		exit();
	}

    /**
     *
     */
    protected function deliverVideo() {
        $event_id = $_GET['event_id'];
        $mid = $_GET['mid'];
        $xoctEvent = xoctEvent::find($event_id);
        $media = $xoctEvent->getFirstPublicationMetadataForUsage(xoctPublicationUsage::getUsage(xoctPublicationUsage::USAGE_PLAYER))->getMedia();
        foreach ($media as $medium) {
            if ($medium->getId() == $mid) {
                $url = $medium->getUrl();
                break;
            }
        }
        if (xoctConf::getConfig(xoctConf::F_SIGN_PLAYER_LINKS)) {
            $url = xoctSecureLink::sign($url);
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
	 *
	 */
	protected function saveAndStay() {
		/**
		 * @var xoctEvent $xoctEvent
		 */
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		if (!ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_EDIT_EVENT, $xoctEvent, $xoctUser)) {
			ilUtil::sendFailure($this->txt('msg_no_access'), true);
			$this->cancel();
		}

		$xoctEventFormGUI = new xoctEventFormGUI($this, xoctEvent::find($_GET[self::IDENTIFIER]), $this->xoctOpenCast);
		$xoctEventFormGUI->setValuesByPost();

		if ($xoctEventFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_success'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_EDIT);
		}
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function update() {
		/**
		 * @var xoctEvent $xoctEvent
		 */
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		if (!ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_EDIT_EVENT, $xoctEvent, $xoctUser)) {
			ilUtil::sendFailure($this->txt('msg_no_access'), true);
			$this->cancel();
		}

		$xoctEventFormGUI = new xoctEventFormGUI($this, xoctEvent::find($_GET[self::IDENTIFIER]), $this->xoctOpenCast);
		$xoctEventFormGUI->setValuesByPost();

		if ($xoctEventFormGUI->saveObject()) {
			ilUtil::sendSuccess($this->txt('msg_success'), true);
			self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
		}
		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function removeInvitations() {
		foreach (xoctInvitation::get() as $xoctInvitation) {
			$xoctInvitation->delete();
		}
		ilUtil::sendSuccess($this->txt('msg_success'), true);
		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function clearAllClips() {
		$filter = array( 'series' => $this->xoctOpenCast->getSeriesIdentifier() );
		$a_data = xoctEvent::getFiltered($filter, NULL, NULL);
		/**
		 * @var $xoctEvent      xoctEvent
		 * @var $xoctInvitation xoctInvitation
		 * @var $xoctGroup      xoctIVTGroup
		 */
		foreach ($a_data as $i => $d) {
			$xoctEvent = xoctEvent::find($d['identifier']);
			$xoctEvent->setTitle('Clip ' . $i);
			$xoctEvent->setDescription('Subtitle ' . $i);
			$xoctEvent->setPresenter('Presenter ' . $i);
			$xoctEvent->setLocation('Station ' . $i);
			$xoctEvent->setCreated(new DateTime());
			$xoctEvent->removeOwner();
			$xoctEvent->removeAllOwnerAcls();
			$xoctEvent->update();
			foreach (xoctInvitation::where(array( 'event_identifier' => $xoctEvent->getIdentifier() ))->get() as $xoctInvitation) {
				$xoctInvitation->delete();
			}
		}
		foreach (xoctIVTGroup::where(array( 'serie_id' => $this->xoctOpenCast->getObjId() ))->get() as $xoctGroup) {
			$xoctGroup->delete();
		}

		$this->cancel();
	}


	/**
	 *
	 */
	protected function resetPermissions() {
		$filter = array( 'series' => $this->xoctOpenCast->getSeriesIdentifier() );
		$a_data = xoctEvent::getFiltered($filter, NULL, NULL);
		/**
		 * @var $xoctEvent      xoctEvent
		 * @var $xoctInvitation xoctInvitation
		 * @var $xoctGroup      xoctIVTGroup
		 */
		$errors = 'Folgende Clips konnten nicht upgedatet werde: ';
		foreach ($a_data as $i => $d) {
			$xoctEvent = xoctEvent::find($d['identifier']);
			try {
				$xoctEvent->update();
			} catch (xoctException $e) {
				$errors .= $xoctEvent->getTitle() . '; ';
			}
		}
		$this->cancel();
	}


	/**
	 *
	 */
	protected function confirmDelete() {
		/**
		 * @var xoctEvent $xoctEvent
		 */
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		if (!ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_DELETE_EVENT, $xoctEvent, $xoctUser)) {
			ilUtil::sendFailure($this->txt('msg_no_access'), true);
			$this->cancel();
		}
		$ilConfirmationGUI = new ilConfirmationGUI();
		$ilConfirmationGUI->setFormAction(self::dic()->ctrl()->getFormAction($this));
        if (count($xoctEvent->getPublications()) && xoctConf::getConfig(xoctConf::F_WORKFLOW_UNPUBLISH)) {
            $header_text = $this->txt('unpublish_confirm');
            $action_text = 'unpublish';
        } else {
            $header_text = $this->xoctOpenCast->getDuplicatesOnSystem() ? $this->txt('delete_confirm_w_duplicates') : $this->txt('delete_confirm');
            $action_text = 'delete';
        }
		$ilConfirmationGUI->setHeaderText($header_text);
		$ilConfirmationGUI->setCancel($this->txt('cancel'), self::CMD_CANCEL);
		$ilConfirmationGUI->setConfirm($this->txt($action_text), self::CMD_DELETE);
		$ilConfirmationGUI->addItem(self::IDENTIFIER, $xoctEvent->getIdentifier(), $xoctEvent->getTitle());
		self::dic()->mainTemplate()->setContent($ilConfirmationGUI->getHTML());
	}


    /**
     * @throws xoctException
     */
	protected function delete() {
		$xoctEvent = xoctEvent::find($_POST[self::IDENTIFIER]);
		$xoctUser = xoctUser::getInstance(self::dic()->user());
		if (!ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_DELETE_EVENT, $xoctEvent, $xoctUser)) {
			ilUtil::sendFailure($this->txt('msg_no_access'), true);
			$this->cancel();
		}
        if (count($xoctEvent->getPublications()) && xoctConf::getConfig(xoctConf::F_WORKFLOW_UNPUBLISH)) {
            try {
                $xoctEvent->unpublish();
                ilUtil::sendSuccess($this->txt('msg_unpublish_started'), true);
            } catch (xoctException $e) {
                if ($e->getCode() == 409) {
                    ilUtil::sendInfo($this->txt('msg_currently_unpublishing'), true);
                } else {
                    throw $e;
                }
            }
        } else {
            $xoctEvent->delete();
            ilUtil::sendSuccess($this->txt('msg_deleted'), true);
        }
		$this->cancel();
	}


	/**
	 *
	 */
	protected function view() {
		$xoctEvent = xoctEvent::find($_GET[self::IDENTIFIER]);
		echo '<pre>' . print_r($xoctEvent, 1) . '</pre>';
		exit;
		//		$xoctEventFormGUI = new xoctEventFormGUI($this, $xoctEvent, $this->xoctOpenCast, true);
		//		$xoctEventFormGUI->fillForm();
		//		self::dic()->mainTemplate()->setContent($xoctEventFormGUI->getHTML());
	}


	/**
	 *
	 */
	protected function search() {
		/**
		 * @var $event xoctEvent
		 */
		$form = new ilPropertyFormGUI();
		$form->setFormAction(self::dic()->ctrl()->getFormAction($this));
		$form->addCommandButton('import', 'Import');
		$self = new ilSelectInputGUI('import_identifier', 'import_identifier');

		$request = xoctRequest::root()->events()->parameter('limit', 1000);
		$data = json_decode($request->get());
		$ids = array();
		foreach ($data as $d) {
			$event = xoctEvent::find($d->identifier);
			$ids[$event->getIdentifier()] = $event->getTitle() . ' (...' . substr($event->getIdentifier(), - 4, 4) . ')';
		}
		array_multisort($ids);

		$self->setOptions($ids);
		$form->addItem($self);
		self::dic()->mainTemplate()->setContent($form->getHTML());
	}


	/**
	 *
	 */
	protected function import() {
		/**
		 * @var $event xoctEvent
		 */
		// $event = xoctEvent::find($_POST['import_identifier']);
		$event = xoctEvent::find($_POST['import_identifier']);
		$html = 'Series before set: ' . $event->getSeriesIdentifier() . '<br>';
		$event->setSeriesIdentifier($this->xoctOpenCast->getSeriesIdentifier());
		$html .= 'Series after set: ' . $event->getSeriesIdentifier() . '<br>';
		//		$event->updateSeries();
		$event->updateSeries();
		$html .= 'Series after update: ' . $event->getSeriesIdentifier() . '<br>';
		//		echo '<pre>' . print_r($event, 1) . '</pre>';
		$event = new xoctEvent($_POST['import_identifier']);
		$html .= 'Series after new read: ' . $event->getSeriesIdentifier() . '<br>';

		//		$html .= 'POST: ' . $_POST['import_identifier'];
		self::dic()->mainTemplate()->setContent($html);
		//		self::dic()->ctrl()->redirect($this, self::CMD_STANDARD);
	}


	/**
	 *
	 */
	protected function listAll() {
		/**
		 * @var $event xoctEvent
		 */
		$request = xoctRequest::root()->events()->parameter('limit', 1000);
		$content = '';
		foreach (json_decode($request->get()) as $d) {
			$event = xoctEvent::find($d->identifier);
			$content .= '<pre>' . print_r($event->__toStdClass(), 1) . '</pre>';
		}
		self::dic()->mainTemplate()->setContent($content);
	}


	/**
	 *
	 */
	protected function clearCache() {
		xoctCacheFactory::getInstance()->flush();
		$this->xoctOpenCast->getSeriesIdentifier();
		self::dic()->ctrl()->redirect($this, self::CMD_SHOW_CONTENT);
	}

	/**
	 * @return string
	 */
	protected function getModalsHTML() {
		$modal_date_html = $modal_quality_html = '';
		if (ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_REPORT_DATE_CHANGE)) {
			$modal_date = new xoctReportingModalGUI($this, xoctReportingModalGUI::REPORTING_TYPE_DATE);
			$modal_date_html = $modal_date->getHTML();
		}
		if (xoctConf::getConfig(xoctConf::F_REPORT_QUALITY)) {
			$modal_quality = new xoctReportingModalGUI($this, xoctReportingModalGUI::REPORTING_TYPE_QUALITY);
			$modal_quality_html = $modal_quality->getHTML();
		}

		return $modal_date_html . $modal_quality_html;
	}


	/**
	 *
	 */
	protected function reportDate() {
		if (ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_REPORT_DATE_CHANGE)) {
            $message = $this->getDateReportMessage($_POST['message']);
            $subject = 'ILIAS Opencast Plugin: neue Meldung «geplante Termine anpassen»';
            $report = new xoctReport();
            $report->setType(xoctReport::TYPE_DATE)
                ->setUserId(self::dic()->user()->getId())
                ->setSubject($subject)
                ->setMessage($message)
                ->create();
        }
		ilUtil::sendSuccess(self::plugin()->translate('msg_date_report_sent'), true);
		self::dic()->ctrl()->redirect($this);
	}


	/**
	 *
	 */
	protected function reportQuality() {
		$event = new xoctEvent($_POST['event_id']);
		if (ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_REPORT_QUALITY_PROBLEM, $event)) {
            $message = $this->getQualityReportMessage($event, $_POST['message']);
            $subject = 'ILIAS Opencast Plugin: neue Meldung «Qualitätsprobleme»';

            $report = new xoctReport();
            $report->setType(xoctReport::TYPE_QUALITY)
                ->setUserId(self::dic()->user()->getId())
                ->setSubject($subject)
                ->setMessage($message)
                ->create();
		}
		ilUtil::sendSuccess(self::plugin()->translate('msg_quality_report_sent'), true);
		self::dic()->ctrl()->redirect($this);
	}

    /**
     * @param xoctEvent $event
     * @param $message
     * @return string
     */
    protected function getQualityReportMessage(xoctEvent $event, $message) {
        $link = ilLink::_getStaticLink($_GET['ref_id'], ilOpenCastPlugin::PLUGIN_ID,
            true);
        $link = '<a href="' . $link . '">' . $link . '</a>';
        $series = xoctInternalAPI::getInstance()->series()->read($_GET['ref_id']);
        $crs_grp_role = ilObjOpenCast::_getCourseOrGroupRole();
	    $mail_body =
            "Dies ist eine automatische Benachrichtigung des ILIAS Opencast Plugins <br><br>"
            . "Es gab eine neue Meldung im Bereich «Qualitätsprobleme melden». <br><br>"
            . "<b>Benutzer/in:</b> " . self::dic()->user()->getLogin() . ", " . self::dic()->user()->getEmail() . " <br>"
            . "<b>Rolle im ILIAS-Kurs:</b> $crs_grp_role <br><br>"
            . "<b>Opencast Serie in ILIAS:</b> $link<br>"
            . "<b>Titel Opencast Event:</b> {$event->getTitle()}<br>"
            . "<b>ID Opencast Event:</b> {$event->getIdentifier()}<br>"
            . "<b>Titel Opencast Serie:</b> {$series->getILIASObject()->getTitle()}<br>"
            . "<b>ID Opencast Serie:</b> {$series->getSeriesIdentifier()}<br><br>"
            . "<b>Nachrichtentext:</b> <br>"
            . "<hr>"
            . nl2br($message) . "<br>"
            . "<hr>";
	    return $mail_body;
    }

    /**
     * @param $message
     * @return string
     */
    protected function getDateReportMessage($message) {
        $link = ilLink::_getStaticLink($_GET['ref_id'], ilOpenCastPlugin::PLUGIN_ID,
            true);
        $link = '<a href="' . $link . '">' . $link . '</a>';
        $series = xoctInternalAPI::getInstance()->series()->read($_GET['ref_id']);
        $mail_body =
            "Dies ist eine automatische Benachrichtigung des ILIAS Opencast Plugins <br><br>"
            . "Es gab eine neue Meldung im Bereich «geplante Termine anpassen». <br><br>"
            . "<b>Benutzer/in:</b> " . self::dic()->user()->getLogin() . ", " . self::dic()->user()->getEmail() . " <br><br>"
            . "<b>Opencast Serie in ILIAS:</b> $link<br>"
            . "<b>Titel Opencast Serie:</b> {$series->getILIASObject()->getTitle()}<br>"
            . "<b>ID Opencast Serie:</b> {$series->getSeriesIdentifier()}<br><br>"
            . "<b>Nachrichtentext:</b> <br>"
            . "<hr>"
            . nl2br($message) . "<br>"
            . "<hr>";
        return $mail_body;
    }


	/**
	 * @param $key
	 *
	 * @return string
	 * @throws \srag\DIC\OpenCast\Exception\DICException
	 */
	public function txt($key) {
		return self::plugin()->translate('event_' . $key);
	}


	/**
	 * @return int
	 */
	public function getObjId() {
		return $this->xoctOpenCast->getObjId();
	}
}
