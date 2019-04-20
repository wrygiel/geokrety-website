#!/bin/bash
cat <<EOF | curl --data-binary @- http://gk:9091/metrics/job/errory
# TYPE errory_metric counter
# HELP errory_metric Our user encounter an error.
errory_metric{level="warn"} 1
EOF