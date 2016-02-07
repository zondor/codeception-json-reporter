<?php
namespace Codeception\Reporter;

use Codeception\PHPUnit\ResultPrinter as CodeceptionResultPrinter;
use Codeception\Step;

class Json extends CodeceptionResultPrinter
{
    protected $wholeData = [];
    protected $suite = [];
    protected $scenario = [];
    protected $scenarioId = 0;
    protected $filePath;
    protected $timeTaken = 0;

    protected $failures = [];

    public function __construct($out = null)
    {
        parent::__construct($out);
        $this->filePath = $out;
    }

    private function addSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->wholeData['suite'][$suite->getName()] = [
            'name'      => $suite->getName(),
            'groups'    => $suite->getGroups(),
            'scenarios' => [],
        ];
        $this->scenarioId = 0;
        $this->scenario = &$this->wholeData['suite'][$suite->getName()]['scenarios'];

    }

    /**
     * @param \PHPUnit_Framework_TestSuite $suite
     */
    public function startTestSuite(\PHPUnit_Framework_TestSuite $suite)
    {
        $this->addSuite($suite);
    }

    /**
     * Handler for 'on test' event.
     *
     * @param string $name
     * @param boolean $success
     * @param array $steps
     */
    protected function onTest($name, $success = true, array $steps = [], $time = 0)
    {
        $this->timeTaken += $time;

        switch ($this->testStatus) {
            case \PHPUnit_Runner_BaseTestRunner::STATUS_FAILURE:
                $scenarioStatus = 'failed';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_SKIPPED:
                $scenarioStatus = 'skipped';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_INCOMPLETE:
                $scenarioStatus = 'incomplete';
                break;
            case \PHPUnit_Runner_BaseTestRunner::STATUS_ERROR:
                $scenarioStatus = 'failed';
                break;
            default:
                $scenarioStatus = 'success';
        }

        $stepsBuffer = [];
        $metaStep = null;
        $stepsId = 0;

        /**
         * @var $step Step
         */

        foreach ($steps as $step) {

            $stepsId++;
            $stepsBuffer[$stepsId] = [
                'txt'      => (string)$step,
                'html'     => $step->getHtml(),
                'status'   => $step->hasFailed() ? "failed" : "success",
                'metaStep' => null,
            ];


            if ($step->getMetaStep()) {
                $stepsBuffer[$stepsId]['metaStep'] = [
                    'name'  => $metaStep,
                    'steps' => (array)$step,
                    'id'    => uniqid(),
                ];
                continue;
            }
        };
        $this->scenarioId++;
        $this->scenario[$this->scenarioId."->".ucfirst($name)] =
            [
                'name'           => ucfirst($name),
                'scenarioStatus' => $scenarioStatus,
                'steps'          => $stepsBuffer,
                'failed'         => !$success,
                'failure'        => $this->lastFailure,
                'time'           => $time,
            ];
        $this->lastFailure = null;
    }


    protected function endRun()
    {
        $this->wholeData['run'] = [
            'name'                => 'Codeception Results',
            'status'              => !$this->failed,
            'time'                => $this->timeTaken,
            'successfulScenarios' => $this->successful,
            'failedScenarios'     => $this->failed,
            'skippedScenarios'    => $this->skipped,
            'incompleteScenarios' => $this->incomplete,
            'failures'            => $this->failures,
        ];

        file_put_contents($this->filePath, json_encode($this->wholeData, JSON_BIGINT_AS_STRING));
    }

    public function addError(\PHPUnit_Framework_Test $test, \Exception $e, $time)
    {
        $this->logError($test, $e);
        parent::addError($test, $e, $time);
    }

    public function addFailure(\PHPUnit_Framework_Test $test, \PHPUnit_Framework_AssertionFailedError $e, $time)
    {
        $this->logError($test, $e);
        parent::addFailure($test, $e, $time);
    }

    private function logError(\PHPUnit_Framework_Test $test, \Exception $e)
    {
        $this->lastFailure = $e->getMessage();
        $this->failures[$test->toString()] = $e->getMessage();
    }
}
