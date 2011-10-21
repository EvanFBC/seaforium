import requests
import lxml

from urlparse import urlparse
from lxml import html

class OldYayClient:

    @staticmethod
    def read_last_pm_link(username, password):
        host = 'http://yayhooray.com'
        r = requests.post(host, dict(username=username,
                                     password=password,
                                     action='login'))
        messages = requests.get("%s/messages" % host, cookies=r.cookies)
        tree = lxml.html.fromstring(messages.content)
        message_url = tree.find_class("message")[0].cssselect("a")[0].get('href')
        message = requests.get("%s/%s" % (host, message_url), cookies=r.cookies)
        tree2 = lxml.html.fromstring(message.content)
        return tree2.cssselect(".messageoriginal a")[0].get('href')


class YayClient:

    def __init__(self, options):
        self.options = options

    @staticmethod
    def register(details, username, email, password, confirm_password):
        creds = dict(username = username,
                     email = email,
                     password = password,
                     password_confirm = confirm_password)
        return requests.post(details['url'] + 'beta/register', creds)

    @staticmethod
    def login(details, username, password):
        creds = dict(username = username, password = password)
        return requests.post(details['url'] + 'beta/login', creds)


    @staticmethod
    def forgot_password(opts, email):
        keyreq = requests.get(opts['url'] + 'beta/forgot_password')
        tree = lxml.html.fromstring(keyreq.content)
        key = tree.get_element_by_id('forgot-key').value
        creds = dict(email = email, key = key)
        return requests.post(opts['url'] + 'beta/forgot_password', creds)


    @staticmethod
    def is_logged_in(details, cookies):
        r = requests.get(details['url'] + 'f/discussions', cookies=cookies)
        tree = lxml.html.fromstring(r.content)
        return not not tree.cssselect(".welcome")

