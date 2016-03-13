# Filternet
A simple utility to check whether the given url/domain is blocked in Iran.

## Installation

Run the following command to install this [package]():

~~~bash
composer global require filternet/filternet
~~~

## Usage

#### DNS

~~~bash
filternet check:dns youtube.com -s 8.8.8.8
~~~

Result:

~~~
 2/2 [============================] 100% Done!
+-------------+-----------------+---------+---------------------+
| Domain      | IP              | Status  | Date                |
+-------------+-----------------+---------+---------------------+
| youtube.com | 10.10.34.36     | Blocked | 2016-03-14 01:27:58 |
| YOUTUBE.COM | 173.194.116.195 | Open    | 2016-03-14 01:27:58 |
+-------------+-----------------+---------+---------------------+
~~~

#### HTTP

~~~bash
filternet check:http http://dropbox.com
~~~

Result:

~~~
 100/100 [============================] 100% Done!
+--------------------+------------------------+-------+---------+---------------------+
| Url                | HTTP Response Status   | Title | Status  | Date                |
+--------------------+------------------------+-------+---------+---------------------+
| http://dropbox.com | HTTP/1.0 403 Forbidden | M4-8  | Blocked | 2016-03-14 01:30:18 |
+--------------------+------------------------+-------+---------+---------------------+
~~~

#### TLS SNI (Server Name Indication)

~~~bash
filternet check:sni twitter.com
~~~

~~~
 2/2 [============================] 100% Done!
+-------------+---------+---------------------+
| SNI Name    | Status  | Date                |
+-------------+---------+---------------------+
| twitter.com | Blocked | 2016-03-14 01:34:40 |
| TWITTER.COM | Open    | 2016-03-14 01:34:40 |
+-------------+---------+---------------------+
~~~

## License

This project is released under the [MIT](https://github.com/alibo/filternet/blob/master/LICENSE) License.
