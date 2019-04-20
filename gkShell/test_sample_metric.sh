#!/bin/bash
echo "some_metric 3.14" | curl --data-binary @- http://gk:9091/metrics/job/some_job


echo "pocmetric 7" | curl --data-binary @- http://192.168.99.100:9091/metrics/job/mypoc