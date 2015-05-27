<?php


/**
 * 
 */
class xoctRequest {
    const XDGL_ID = 0;

    const STATUS_NEW = 'STATUS_NEW';
    const STATUS_IN_PROGRRESS = 'STATUS_IN_PROGRRESS';
    const STATUS_REFUSED = 'STATUS_REFUSED';
    const STATUS_RELEASED = 'STATUS_RELEASED';
    const STATUS_COPY = 'STATUS_COPY';

    /**
     * @var int
     */
    protected $id = 0;

    /**
     * @var string
     */
    protected $status = "";

    /**
     * @var int
     */
    protected $create_date = 0;

    /**
     * @var int
     */
    protected $date_last_status_change = 0;

    /**
     * @var string
     */
    protected $absolute_file_path = "";

    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var string
     */
    protected $author = "";

    /**
     * @var string
     */
    protected $book = "";

    /**
     * @var int
     */
    protected $publishing_year = 0;

    /**
     * @var int
     */
    protected $pages = 0;

    public function __construct() {
	}


	/**
	 * 
	 */
	public function get() {
		// TODO implement here
	}

	/**
	 * 
	 */
	public function post() {
		// TODO implement here
	}

	/**
	 * 
	 */
	public function put() {
		// TODO implement here
	}

	/**
	 * 
	 */
	public function delete() {
		// TODO implement here
	}

    /**
     * @param string $absolute_file_path
     */
    public function setAbsoluteFilePath($absolute_file_path)
    {
        $this->absolute_file_path = $absolute_file_path;
    }

    /**
     * @return string
     */
    public function getAbsoluteFilePath()
    {
        return $this->absolute_file_path;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param int $create_date
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
    }

    /**
     * @return int
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param int $date_last_status_change
     */
    public function setDateLastStatusChange($date_last_status_change)
    {
        $this->date_last_status_change = $date_last_status_change;
    }

    /**
     * @return int
     */
    public function getDateLastStatusChange()
    {
        return $this->date_last_status_change;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $book
     */
    public function setBook($book)
    {
        $this->book = $book;
    }

    /**
     * @return string
     */
    public function getBook()
    {
        return $this->book;
    }

    /**
     * @param int $pages
     */
    public function setPages($pages)
    {
        $this->pages = $pages;
    }

    /**
     * @return int
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * @param int $publishing_year
     */
    public function setPublishingYear($publishing_year)
    {
        $this->publishing_year = $publishing_year;
    }

    /**
     * @return int
     */
    public function getPublishingYear()
    {
        return $this->publishing_year;
    }
}