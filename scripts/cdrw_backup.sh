#!/bin/bash
# $Id$
# $Author$
#
# Script for the cdrw_backup module. Params:
# 	cdrw_backup.sh (device) (driver) (speed)

# TODO: exclude CVS directories, pretty up the output, maybe i18n

DEV=$1
DRIVER=$2
SPEED=$3

# Get datestamp
DATESTAMP=`date +%Y%m%d`

# Make temporary ISO path
TMPPATH=/tmp/freemed_cdrw_${PPID}
rm -Rf ${TMPPATH}
mkdir -p ${TMPPATH}

# Create bz2 tar of freemed data
( cd /var/lib/mysql/freemed; tar cjvf ${TMPPATH}/database_${DATESTAMP}.tar.bz2 * 2>&1 > /dev/null )

# Use mkisofs to generate the image
#	-quiet \
mkisofs -o ${TMPPATH}/${DATESTAMP}.iso \
	-V FREEMED_${DATESTAMP} \
	-p "FreeMED CDRW Backup Module" \
	-R -T -l -J -max-iso9660-filenames -graft-points \
	database_${DATESTAMP}.tar.bz2=${TMPPATH}/database_${DATESTAMP}.tar.bz2 \
	freemed=/usr/share/freemed 2>&1

if [ ! -f ${TMPPATH}/${DATESTAMP}.iso ]; then
	echo "Could not properly generate ISO image for backup!"
	exit;
fi

# Burn the actual image to CD, and eject
cdrecord -eject dev=${DEV} speed=${SPEED} driver=${DRIVER} \
	-data ${TMPPATH}/${DATESTAMP}.iso
