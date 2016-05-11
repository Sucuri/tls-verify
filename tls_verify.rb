require 'excon'
require 'httpclient'
require 'curl'
require "openssl"
require 'net/http'
require 'http'
require 'json'

def net_http url
  Net::HTTP.get URI(url)
end

def excon url
  Excon.get url
end

def httpclient url
  HTTPClient.get url
end

def curb url
  Curl.get url
end

def httprb url
  HTTP.get url
end

urls = {
    revoked: 'https://revoked.grc.com/',
    incomplete_chain: 'https://incomplete-chain.badssl.com/',
    expired: 'https://qvica1g3-e.quovadisglobal.com/',
    expired2: 'https://expired.badssl.com/',
    self_signed: 'https://self-signed.badssl.com/',
    bad_domain: 'https://wrong.host.badssl.com/',
    bad_domain2: 'https://tv.eurosport.com/',
    rc4: 'https://rc4.badssl.com/',
    dh480: 'https://dh480.badssl.com/',
    superfish: 'https://superfish.badssl.com/',
    edellroot: 'https://edellroot.badssl.com/',
    dsdtestprovider: 'https://dsdtestprovider.badssl.com/'
}

methods = [:excon, :net_http, :httpclient, :curb, :httprb]

methods.each do |method|
  urls.each do |name, url|
    begin
      send(method, url)
      puts "Error expected for #{name} when using #{method}"
    rescue Exception => e
      # puts "Error occurred when using #{method} for #{name} at #{url}:"
    end
  end
end
