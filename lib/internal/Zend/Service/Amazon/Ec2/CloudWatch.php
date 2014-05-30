<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: CloudWatch.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Service_Amazon_Ec2_Abstract
 */
#require_once 'Zend/Service/Amazon/Ec2/Abstract.php';

/**
 * An Amazon EC2 interface that allows yout to run, terminate, reboot and describe Amazon
 * Ec2 Instances.
 *
 * @category   Zend
 * @package    Zend_Service_Amazon
 * @subpackage Ec2
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Amazon_Ec2_CloudWatch extends Zend_Service_Amazon_Ec2_Abstract
{
    /**
     * The HTTP query server
     */
    protected $_ec2Endpoint = 'monitoring.amazonaws.com';

    /**
     * The API version to use
     */
    protected $_ec2ApiVersion = '2009-05-15';

    /**
     * XML Namespace for the CloudWatch Stuff
     */
    protected $_xmlNamespace = 'http://monitoring.amazonaws.com/doc/2009-05-15/';

    /**
     * The following metrics are available from each EC2 instance.
     *
     * CPUUtilization: The percentage of allocated EC2 compute units that are
     *  currently in use on the instance. This metric identifies the processing
     *  power required to run an application upon a selected instance.
     *
     * NetworkIn: The number of bytes received on all network interfaces by
     *  the instance. This metric identifies the volume of incoming network
     *  traffic to an application on a single instance.
     *
     * NetworkOut: The number of bytes sent out on all network interfaces
     *  by the instance. This metric identifies the volume of outgoing network
     *  traffic to an application on a single instance.
     *
     * DiskWriteOps: Completed write operations to all hard disks available to
     *  the instance. This metric identifies the rate at which an application
     *  writes to a hard disk. This can be used to determine the speed in which
     *  an application saves data to a hard disk.
     *
     * DiskReadBytes: Bytes read from all disks available to the instance. This
     *  metric is used to determine the volume of the data the application reads
     *  from the hard disk of the instance. This can be used to determine the
     *  speed of the application for the customer.
     *
     * DiskReadOps: Completed read operations from all disks available to the
     *  instances. This metric identifies the rate at which an application reads
     *  a disk. This can be used to determine the speed in which an application
     *  reads data from a hard disk.
     *
     * DiskWriteBytes: Bytes written to all disks available to the instance. This
     *  metric is used to determine the volume of the data the application writes
     *  onto the hard disk of the instance. This can be used to determine the speed
     *  of the application for the customer.
     *
     * Latency: Time taken between a request and the corresponding response as seen
     *  by the load balancer.
     *
     * RequestCount: The number of requests processed by the LoadBalancer.
     *
     * HealthyHostCount: The number of healthy instances. Both Load Balancing dimensions,
     *  LoadBalancerName and AvailabilityZone, should be specified when retreiving
     *  HealthyHostCount.
     *
     * UnHealthyHostCount: The number of unhealthy instances. Both Load Balancing dimensions,
     *  LoadBalancerName and AvailabilityZone, should be specified when retreiving
     *  UnHealthyHostCount.
     *
     * Amazon CloudWatch data for a new EC2 instance becomes available typically
     * within one minute of the end of the first aggregation period for the new
     * instance. You can use the currently available dimensions for EC2 instances
     * along with these metrics in order to refine the slice of data you want returned,
     * such as metric CPUUtilization and dimension ImageId to get all CPUUtilization
     * data for instances using the specified AMI.
     *
     * @var array
     */
    protected $_validMetrics = array('CPUUtilization', 'NetworkIn', 'NetworkOut',
                                    'DiskWriteOps', 'DiskReadBytes', 'DiskReadOps',
                                    'DiskWriteBytes', 'Latency', 'RequestCount',
                                    'HealthyHostCount', 'UnHealthyHostCount');

    /**
     * Amazon CloudWatch not only aggregates the raw data coming in, it also computes
     * several statistics on the data. The following table lists the statistics that you can request:
     *
     * Minimum: The lowest value observed during the specified period. This can be used to
     *  determine low volumes of activity for your application.
     *
     * Maximum: The highest value observed during the specified period. You can use this to
     *  determine high volumes of activity for your application.
     *
     * Sum: The sum of all values received (if appropriate, for example a rate would not be
     *  summed, but a number of items would be). This statistic is useful for determining
     *  the total volume of a metric.
     *
     * Average: The Average of all values received during the specified period. By comparing
     *  this statistic with the minimum and maximum statistics, you can determine the full
     *  scope of a metric and how close the average use is to the minimum and the maximum.
     *  This will allow you to increase or decrease your resources as needed.
     *
     * Samples: The count (number) of measures used. This statistic is always returned to
     *  show the user the size of the dataset collected. This will allow the user to properly
     *  weight the data.
     *
     * Statistics are computed within a period you specify, such as all CPUUtilization within a
     * five minute period. At a minimum, all data is aggregated into one minute intervals. This
     * is the minimum resolution of the data. It is this data that can be aggregated into larger
     * periods of time that you request.
     *
     * Aggregate data is generally available from the service within one minute from the end of the
     * aggregation period. Delays in data propagation might cause late or partially late data in
     * some cases. If your data is delayed, you should check the service’s Health Dashboard for
     * any current operational issues with either Amazon CloudWatch or the services collecting
     * the data, such as EC2 or Elastic Load Balancing.
     *
     * @var array
     */
    protected $_validStatistics = array('Average', 'Maximum', 'Minimum', 'Samples', 'Sum');

    /**
     * Valid Dimention Keys for getMetricStatistics
     *
     * ImageId: This dimension filters the data you request for all instances running
     *  this EC2 Amazon Machine Image (AMI).
     *
     * AvailabilityZone: This dimension filters the data you request for all instances
     *  running in that EC2 Availability Zone.
     *
     * AutoScalingGroupName: This dimension filters the data you request for all instances
     *  in a specified capacity group. An AutoScalingGroup is a collection of instances
     *  defined by customers of the Auto Scaling service. This dimension is only available
     *  for EC2 metrics when the instances are in such an AutoScalingGroup.
     *
     * InstanceId: This dimension filters the data you request for only the identified
     *  instance. This allows a user to pinpoint an exact instance from which to monitor data.
     *
     * InstanceType: This dimension filters the data you request for all instances running
     *  with this specified instance type. This allows a user to catagorize his data by the
     *  type of instance running. For example, a user might compare data from an m1.small instance
     *  and an m1.large instance to determine which has the better business value for his application.
     *
     * LoadBalancerName: This dimension filters the data you request for the specified LoadBalancer
     *  name. A LoadBalancer is represented by a DNS name and provides the single destination to
     *  which all requests intended for your application should be directed. This metric allows
     *  you to examine data from all instances connected to a single LoadBalancer.
     *
     * @var array
     */
    protected $_validDimensionsKeys = array('ImageId', 'AvailabilityZone', 'AutoScalingGroupName',
                                            'InstanceId', 'InstanceType', 'LoadBalancerName');

    /**
     * Returns data for one or more statistics of given a metric
     *
     * Note:
     * The maximum number of datapoints that the Amazon CloudWatch service will
     * return in a single GetMetricStatistics request is 1,440. If a request is
     * made that would generate more datapoints than this amount, Amazon CloudWatch
     * will return an error. You can alter your request by narrowing the time range
     * (StartTime, EndTime) or increasing the Period in your single request. You may
     * also get all of the data at the granularity you originally asked for by making
     * multiple requests with adjacent time ranges.
     *
     * @param array $options            The options you want to get statistics for:
     *                                  ** Required **
     *                                  MeasureName: The measure name that corresponds to
     *                                      the measure for the gathered metric. Valid EC2 Values are
     *                                      CPUUtilization, NetworkIn, NetworkOut, DiskWriteOps
     *                                      DiskReadBytes, DiskReadOps, DiskWriteBytes. Valid Elastic
     *                                      Load Balancing Metrics are Latency, RequestCount, HealthyHostCount
     *                                      UnHealthyHostCount
     *                                  Statistics: The statistics to be returned for the given metric. Valid
     *                                      values are Average, Maximum, Minimum, Samples, Sum.  You can specify
     *                                      this as a string or as an array of values.  If you don't specify one
     *                                      it will default to Average instead of failing out.  If you specify an incorrect
     *                                      option it will just skip it.
     *                                  ** Optional **
     *                                  Dimensions: Amazon CloudWatch allows you to specify one Dimension to further filter
     *                                      metric data on. If you don't specify a dimension, the service returns the aggregate
     *                                      of all the measures with the given measure name and time range.
     *                                  Unit: The standard unit of Measurement for a given Measure. Valid Values: Seconds,
     *                                      Percent, Bytes, Bits, Count, Bytes/Second, Bits/Second, Count/Second, and None
     *                                      Constraints: When using count/second as the unit, you should use Sum as the statistic
     *                                      instead of Average. Otherwise, the sample returns as equal to the number of requests
     *                                      instead of the number of 60-second intervals. This will cause the Average to
     *                                      always equals one when the unit is count/second.
     *                                  StartTime: The timestamp of the first datapoint to return, inclusive. For example,
     *                                      2008-02-26T19:00:00+00:00. We round your value down to the nearest minute.
     *                                      You can set your start time for more than two weeks in the past. However,
     *                                      you will only get data for the past two weeks. (in ISO 8601 format)
     *                                      Constraints: Must be before EndTime
     *                                  EndTime: The timestamp to use for determining the last datapoint to return. This is
     *                                      the last datapoint to fetch, exclusive. For example, 2008-02-26T20:00:00+00:00.
     *                                      (in ISO 8601 format)
     */
    public function getMetricStatistics(array $options)
    {
        $_usedStatistics = array();

        $params = array();
        $params['Action'] = 'GetMetricStatistics';

        if (!isset($options['Period'])) {
            $options['Period'] = 60;
        }
        if (!isset($options['Namespace'])) {
            $options['Namespace'] = 'AWS/EC2';
        }

        if (!isset($options['MeasureName']) || !in_array($options['MeasureName'], $this->_validMetrics, true)) {
            throw new Zend_Service_Amazon_Ec2_Exception('Invalid Metric Type: ' . $options['MeasureName']);
        }

        if(!isset($options['Statistics'])) {
            $options['Statistics'][] = 'Average';
        } elseif(!is_array($options['Statistics'])) {
            $options['Statistics'][] = $options['Statistics'];
        }

        foreach($options['Statistics'] as $k=>$s) {
            if(!in_array($s, $this->_validStatistics, true)) continue;
            $options['Statistics.member.' . ($k+1)] = $s;
            $_usedStatistics[] = $s;
        }
        unset($options['Statistics']);

        if(isset($options['StartTime'])) {
            if(!is_numeric($options['StartTime'])) $options['StartTime'] = strtotime($options['StartTime']);
            $options['StartTime'] = gmdate('c', $options['StartTime']);
        } else {
            $options['StartTime'] = gmdate('c', strtotime('-1 hour'));
        }

        if(isset($options['EndTime'])) {
            if(!is_numeric($options['EndTime'])) $options['EndTime'] = strtotime($options['EndTime']);
            $options['EndTime'] = gmdate('c', $options['EndTime']);
        } else {
            $options['EndTime'] = gmdate('c');
        }

        if(isset($options['Dimensions'])) {
            $x = 1;
            foreach($options['Dimensions'] as $dimKey=>$dimVal) {
                if(!in_array($dimKey, $this->_validDimensionsKeys, true)) continue;
                $options['Dimensions.member.' . $x . '.Name'] = $dimKey;
                $options['Dimensions.member.' . $x . '.Value'] = $dimVal;
                $x++;
            }
            
            unset($options['Dimensions']);
        }

        $params = array_merge($params, $options);

        $response = $this->sendRequest($params);
        $response->setNamespace($this->_xmlNamespace);

        $xpath = $response->getXPath();
        $nodes = $xpath->query('//ec2:GetMetricStatisticsResult/ec2:Datapoints/ec2:member');

        $return = array();
        $return['label'] = $xpath->evaluate('string(//ec2:GetMetricStatisticsResult/ec2:Label/text())');
        foreach ( $nodes as $node ) {
            $item = array();

            $item['Timestamp'] = $xpath->evaluate('string(ec2:Timestamp/text())', $node);
            $item['Unit'] = $xpath->evaluate('string(ec2:Unit/text())', $node);
            $item['Samples'] = $xpath->evaluate('string(ec2:Samples/text())', $node);
            foreach($_usedStatistics as $us) {
                $item[$us] = $xpath->evaluate('string(ec2:' . $us . '/text())', $node);
            }

            $return['datapoints'][] = $item;
            unset($item, $node);
        }

        return $return;

    }

    /**
     * Return the Metrics that are aviable for your current monitored instances
     *
     * @param string $nextToken     The NextToken parameter is an optional parameter
     *                                 that allows you to retrieve the next set of results
     *                                 for your ListMetrics query.
     * @return array
     */
    public function listMetrics($nextToken = null)
    {
        $params = array();
        $params['Action'] = 'ListMetrics';
        if (!empty($nextToken)) {
            $params['NextToken'] = $nextToken;
        }

        $response = $this->sendRequest($params);
        $response->setNamespace($this->_xmlNamespace);

        $xpath = $response->getXPath();
        $nodes = $xpath->query('//ec2:ListMetricsResult/ec2:Metrics/ec2:member');

        $return = array();
        foreach ( $nodes as $node ) {
            $item = array();

            $item['MeasureName'] = $xpath->evaluate('string(ec2:MeasureName/text())', $node);
            $item['Namespace'] = $xpath->evaluate('string(ec2:Namespace/text())', $node);
            $item['Deminsions']['name'] = $xpath->evaluate('string(ec2:Dimensions/ec2:member/ec2:Name/text())', $node);
            $item['Deminsions']['value'] = $xpath->evaluate('string(ec2:Dimensions/ec2:member/ec2:Value/text())', $node);

            if (empty($item['Deminsions']['name'])) {
                $item['Deminsions'] = array();
            }

            $return[] = $item;
            unset($item, $node);
        }

        return $return;
    }
}
