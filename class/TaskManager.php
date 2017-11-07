<?php
/**
 * Created by PhpStorm.
 * User: simon
 * Date: 06.11.17
 * Time: 21:44
 */

class TaskManager
{
    private static $LOGDIR = "../tasklog";

    public static function get($cmd,$taskname){
        $taskname = hash("md5",$taskname);
        switch(TaskManager::isTaskRunning($taskname)){
            case 2:
                //task does not exist, start it
                //echo "task does not exist, start it";
                TaskManager::startTask($cmd,$taskname);
                return ['code'=>2,"log"=>TaskManager::getLog($taskname),"takshash"=>$taskname,"pid"=>$_SESSION["TASKS"][$taskname]];
            case 1:
                //task is running, get log
                //echo "task is running, get log";
                return ['code'=>1,"log"=>TaskManager::getLog($taskname),"takshash"=>$taskname,"pid"=>$_SESSION["TASKS"][$taskname]];
            case 0:
                //task not running, entry will be deleted now, returning the full log
                //echo "task not running, entry will be deleted now, returning the full log";
                $log = TaskManager::getLog($taskname);
                $pid = $_SESSION["TASKS"][$taskname];
                TaskManager::deleteLog($taskname);
                return ['code'=>0,"log"=>$log,"takshash"=>$taskname,"pid"=>$pid];
        }
    }

    /**
     * @param $cmd
     * @param $taskname
     */
    private static function startTask($cmd,$taskname){
        if (!is_dir(TaskManager::$LOGDIR)) {
            mkdir(TaskManager::$LOGDIR, 0777, true);
        }

        $process = new BackgroundProcess($cmd);
        $process->run(TaskManager::$LOGDIR."/".$taskname);
        $pid=$process->getPid();

        $_SESSION["TASKS"][$taskname] =$pid;
    }

    public static function killTask($taskname){
        $process = BackgroundProcess::createFromPID($_SESSION["TASKS"][$taskname]);
        $process->stop();
    }

    /**
     * @param $taskname
     * @return int
     */
    private static function isTaskRunning($taskname){
        if(isset($_SESSION["TASKS"][$taskname])){
            if($_SESSION["TASKS"][$taskname] == 0){
                unset($_SESSION["TASKS"][$taskname]);
            }
        }
        if(isset($_SESSION["TASKS"][$taskname])){
            /** @var BackgroundProcess $process */
            $process = BackgroundProcess::createFromPID($_SESSION["TASKS"][$taskname]);
            if($process->isRunning()){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 2;
        }
    }

    private static function getLog($taskname){
        return file_get_contents(TaskManager::$LOGDIR."/".$taskname);
    }

    private static function deleteLog($taskname){
        unset($_SESSION["TASKS"][$taskname]);
        if(file_exists(TaskManager::$LOGDIR."/".$taskname)){
            unlink(TaskManager::$LOGDIR."/".$taskname);
        }
    }
}

