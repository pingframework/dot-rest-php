assert xpath //div[contains(@class\, 'price2')], exists === false
assert xpath //div[@class='price'], count === 3
assert xpath number((//div[@class='price'])[last()]) === 30.75
assert xpath number((//div[@class='price'])[2]) === 20.5

assert xpath //div[@class='price2'], exists === true
