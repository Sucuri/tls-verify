// Test TLS certificate verification in Google Go
// (c) Sucuri, 2016

package main

import (
	"fmt"
	"net/http"
)

func tryDownload(name, url string) error {
	resp, err := http.Get(url)
	if err != nil {
		return err
	}
	defer resp.Body.Close()
	return nil
}

func main() {
	// Try to download each URL. TLS certificate verification must fail for each URL;
	// print "INCORRECT" if Go allows an invalid certificate

	urls := map[string]string{
		"revoked":         "https://revoked.grc.com/",
		"expired":         "https://qvica1g3-e.quovadisglobal.com/",
		"expired2":        "https://expired.badssl.com/",
		"self-signed":     "https://self-signed.badssl.com/",
		"bad domain":      "https://wrong.host.badssl.com/",
		"bad domain2":     "https://tv.eurosport.com/",
		"rc4":             "https://rc4.badssl.com/",
		"dh480":           "https://dh480.badssl.com/",
		"superfish":       "https://superfish.badssl.com/",
		"edellroot":       "https://edellroot.badssl.com/",
		"dsdtestprovider": "https://dsdtestprovider.badssl.com/",
	}

	for name, url := range urls {
		err := tryDownload(name, url)
		if err == nil {
			fmt.Printf("%s: INCORRECT: expected error\n", name)
			continue
		}

		fmt.Printf("%s: correct (%v)\n", name, err)
	}
}
