from playwright.sync_api import sync_playwright

def run(playwright):
    browser = playwright.chromium.launch()
    page = browser.new_page()

    # Navigate to the groups page
    page.goto("http://localhost:8000/admin/groups.php")

    # Wait for the datatable to load
    page.wait_for_selector('.data-table')

    # Take a screenshot
    page.screenshot(path="jules-scratch/verification/verification.png")

    browser.close()

with sync_playwright() as playwright:
    run(playwright)
