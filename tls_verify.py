# Test TLS certificate verification in Python 2.x and 3.x
# (c) Sucuri, 2016

from __future__ import print_function
import ssl

# Import from urllib.request (Python 3) or from urllib and urllib2 (Python 2)
try:
    from urllib.request import urlopen
    from urllib.error import URLError
    from http.client import HTTPSConnection
    from urllib.parse import urlparse
except ImportError:
    from urllib import urlopen
    from urllib2 import urlopen as urlopen2, URLError
    from httplib import HTTPSConnection
    from urlparse import urlparse

# Import the requests library if it's available
try:
    import requests
except ImportError:
    pass

# Test different methods (urlopen, urlopen2, HTTPSConnection, and Requests library)
def tryurlopen(url):
    return urlopen(url).read()

def tryurlopenwithcontext(url):
    return urlopen(url, context = ssl.create_default_context()).read()

def tryurlopen2(url):
    return urlopen2(url).read()

def tryurlopen2withcontext(url):
    return urlopen2(url, context = ssl.create_default_context()).read()


def tryhttpsconnection(url):
    conn = HTTPSConnection(urlparse(url).netloc)
    conn.request("GET", "/")
    return conn.getresponse().read()

def tryhttpsconnectionwithcontext(url):
    conn = HTTPSConnection(urlparse(url).netloc, context = ssl.create_default_context())
    conn.request("GET", "/")
    return conn.getresponse().read()


def tryrequests(url):
    r = requests.get(url)
    return r.text    
    

# TLS certificate verification must fail for each URL;
# print "INCORRECT" if Python allows an invalid certificate
def printres(func, name, url):
    try:
        res = func(url)
        print('{}: INCORRECT: expected error'.format(name))
    except (URLError, ssl.CertificateError, ssl.SSLError, IOError) as e:
        print('{}: correct'.format(name))


urls = {
    'revoked': 'https://revoked.grc.com/',
    'expired': 'https://qvica1g3-e.quovadisglobal.com/',
    'expired2': 'https://expired.badssl.com/',
    'self-signed': 'https://self-signed.badssl.com/',
    'bad domain': 'https://wrong.host.badssl.com/',
    'bad domain2': 'https://tv.eurosport.com/',
    'rc4': 'https://rc4.badssl.com/',
    'dh480': 'https://dh480.badssl.com/',
    'superfish': 'https://superfish.badssl.com/',
    'edellroot': 'https://edellroot.badssl.com/',
    'dsdtestprovider': 'https://dsdtestprovider.badssl.com/'}


# Find available methods
methods = {'urlopen': tryurlopen, 'HTTPSConnection': tryhttpsconnection}

if 'urlopen2' in dir():
    methods['urlopen2'] = tryurlopen2

if 'create_default_context' in dir(ssl):
    methods['urlopen w/context'] = tryurlopenwithcontext
    methods['HTTPSConnection w/context'] = tryhttpsconnectionwithcontext
    if 'urlopen2' in dir():
        methods['urlopen2 w/context'] = tryurlopen2withcontext

if 'requests' in dir():
    methods['requests'] = tryrequests

# Test each URL for each method
for methodsname, method in methods.items():
    print('=== ' + methodsname + ' ===')
    for name, url in urls.items():
        printres(method, name, url)
