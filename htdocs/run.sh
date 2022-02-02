#!/bin/bash
#script to run TORQUE PBS on CentOS 7

USER=root
HOST=`hostname --long`

# single host configuration
echo $HOST > /var/spool/torque/server_name
echo '$pbsserver '$HOST > /var/spool/torque/mom_priv/config
#echo "$HOST np=8" > /var/spool/torque/server_priv/nodes

#stop pbs_server if it was already running
/usr/local/bin/qterm

# start services
systemctl start trqauthd.service
systemctl start pbs_{mom,server,sched}.service

#create new server with ./run.sh -c
while getopts ":c" option; do
   case $option in
      c) 
	 /usr/local/bin/qterm
         yes | /usr/local/sbin/pbs_server -t create
         continue;;
      \?) # Invalid option
         echo "Error: Invalid option"
         exit;;
   esac
done

#wait
sleep 2

# configure manager/operator user
/usr/local/bin/qmgr -c "set server operators += $USER@$HOST"
/usr/local/bin/qmgr -c "set server managers += $USER@$HOST"

# scheduling options
/usr/local/bin/qmgr -c 'set server scheduling = true'
/usr/local/bin/qmgr -c 'set server keep_completed = 300'

# create the default queue called 'batch'
# this will consist of a single node and
# allow a maximum of 7 jobs to be run at
# one time. This was for a dual quad-core
# desktop machine.
/usr/local/bin/qmgr -c 'create queue batch'
/usr/local/bin/qmgr -c 'set queue batch queue_type = execution'
/usr/local/bin/qmgr -c 'set queue batch started = true'
/usr/local/bin/qmgr -c 'set queue batch enabled = true'
/usr/local/bin/qmgr -c 'set queue batch resources_default.walltime = 72:00:00'
/usr/local/bin/qmgr -c 'set queue batch resources_default.nodes = 1'
/usr/local/bin/qmgr -c 'set queue batch max_running = 7'
/usr/local/bin/qmgr -c 'set server default_queue = batch'

/usr/local/bin/qmgr -c "list server"
/usr/local/bin/qmgr -c "list queue batch"

#see nodes
/usr/local/bin/qnodes

#see jobs
/usr/local/bin/qstat -a
