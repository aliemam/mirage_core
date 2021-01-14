<?php

namespace Mirage\Libs;

use Mirage\App\Job;

class Pipeline
{
    /** @var mixed data to used in all jobs */
    private $data;

    /** @var array each job is a Job Object */
    private array $jobs = [];

    /** @var bool if true, pipeline tries to use parallel extension and do each job in separate thread */
    private bool $async = false;

    /**
     * Pipeline constructor.
     * @param $data
     * @param bool $async
     */
    public function __construct($data, bool $async = false)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * This function returns job id for each Jon Object
     * @param Job $job
     * @param string $id
     * @return Pipeline
     */
    public function addJob(Job $job, string $id): self
    {
        $this->jobs[$id] = $job;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * This first tries to run all jobs in parallel mode (if parallel extension was available and also
     * app.enable_parallel config was true). after that it returns all the returned value from each job->task function.
     * @return array
     */
    public function run(): array
    {
        $return_answers = [];
        if ($this->async && extension_loaded('parallel')) {
            $runtime = [];
            $future = [];
            foreach ($this->jobs as $id => $job) {
                $runtime[$id] = new \parallel\Runtime();
                $future[$id] = $runtime[$id]->run($job->getTaskClosure(), [$this->data]);
            }
            foreach ($future as $id => $f) {
                $return_answers[$id] = $f->value();
            }

        } else {
            foreach ($this->jobs as $id => $job) {
                $return_answers[$id] = $job->task($this->data);
            }
        }

        return $return_answers;
    }
}