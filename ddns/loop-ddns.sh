#!/bin/env bash
DELAY=20
while [ true ]; do
 sleep $DELAY
 ./ddns.sh
done
